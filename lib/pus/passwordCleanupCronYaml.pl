
use strict;
use warnings;
use YAML qw(LoadFile DumpFile);
use Data::Dumper;

# get parameters
my $passwords_yaml = $ARGV[0];
my $complete_yaml = $ARGV[1];

# initialize variables
my $timestamp = time;
my $retention_disable = (60*60*24*186);
my $retention_delete = (60*60*24*246);
my ($tmpuseruac);

# load yaml files
my $passwords = LoadFile($passwords_yaml);
my $yamlcomplete = LoadFile($complete_yaml);



# cleanup old users
foreach my $pw ($passwords) {
  while (my ($key, $value) = each (%{$pw})) {
    if ($key =~ /::password_timestamp$/) {

      # get username of temp-user
      my ($tmpuser) = ($key =~ m/ldap::users::(.*?)::password_timestamp/);
           
      # cleanup md5 passwords
      delete ${$passwords}{"ldap::users::${tmpuser}::password_md5"};

      # [OP-56] skip User with the "Password Never Expired" AD Flag
      $tmpuseruac = ${$yamlcomplete}{"ldap::users::${tmpuser}::userAccountcontrol"};

      # disable user if uac flag is not set
      if (!(defined $tmpuseruac)) {
        ${$passwords}{"ldap::users::${tmpuser}::password_mysql"} = undef;
        ${$passwords}{"ldap::users::${tmpuser}::password_sha256_hex"} = undef;
        ${$passwords}{"ldap::users::${tmpuser}::password_sha512"} = '!';

        # delete entries with no uac flag also!
        if (($value + $retention_delete) < $timestamp ) {
          delete ${$passwords}{"ldap::users::${tmpuser}::password_sha512"};
          delete ${$passwords}{"ldap::users::${tmpuser}::password_mysql"};
          delete ${$passwords}{"ldap::users::${tmpuser}::password_sha256_hex"};
          delete ${$passwords}{"ldap::users::${tmpuser}::password_timestamp"};
          # disable user in password.yaml file if password was last set more than 186 days ago
        }
      } else {

        # skip the user if the UF_DONT_EXPIRE_PASSWD bit is one (see OP-56)
        if ( !(substr($tmpuseruac,15,1) eq "1") ) {

          # delete user from password.yaml file if password was las set more than 246 days ago
          if (($value + $retention_delete) < $timestamp ) {
            delete ${$passwords}{"ldap::users::${tmpuser}::password_sha512"};
            delete ${$passwords}{"ldap::users::${tmpuser}::password_mysql"};
            delete ${$passwords}{"ldap::users::${tmpuser}::password_sha256_hex"};
            delete ${$passwords}{"ldap::users::${tmpuser}::password_timestamp"};
          # disable user in password.yaml file if password was last set more than 186 days ago
          } elsif (($value + $retention_disable) < $timestamp )  {
            ${$passwords}{"ldap::users::${tmpuser}::password_mysql"} = undef;
            ${$passwords}{"ldap::users::${tmpuser}::password_sha256_hex"} = undef;
            ${$passwords}{"ldap::users::${tmpuser}::password_sha512"} = '!';
          }

        }

      }
    }
  }
}

# save yaml file
DumpFile($passwords_yaml, $passwords);
