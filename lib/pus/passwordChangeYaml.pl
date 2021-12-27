
use strict;
use warnings;
use YAML qw(LoadFile DumpFile);
use Data::Dumper;

# get parameters
my $username = $ARGV[0];
my $password_mysql = $ARGV[1];
my $password_sha256 = $ARGV[2];
my $password_sha512 = $ARGV[3];
my $timestamp = $ARGV[4];
my $yamlFile = $ARGV[5];


# load yaml file
my $passwords = LoadFile($yamlFile);

# set password hashes and timestamp for the user
${$passwords}{"ldap::users::${username}::password_sha512"} = $password_sha512;
${$passwords}{"ldap::users::${username}::password_sha256_hex"} = $password_sha256;
${$passwords}{"ldap::users::${username}::password_mysql"} = $password_mysql;
${$passwords}{"ldap::users::${username}::password_timestamp"} = $timestamp;

# save yaml file
DumpFile($yamlFile, $passwords);
