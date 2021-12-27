<?php

  //custom class to overwrite link behaviour, see: https://github.com/cebe/markdown/issues/127
  class TargetBlankMarkdown extends \cebe\markdown\Markdown {
    public $enableNewlines = true;

    protected function renderLink($block) {
      return str_replace('<a ', '<a target="_blank" ', parent::renderLink($block));
    }

    protected function renderAutoUrl($block) {
      return str_replace('<a ', '<a target="_blank" ', parent::renderAutoUrl($block));
    }
  }

  //load markdown file and parse
  $markdown = file_get_contents(PATH . "changelog.md");
  $parser = new TargetBlankMarkdown();
  $output = $parser->parse($markdown);

  include(PATH_STYLE."module".DS."changelog.php");
?>