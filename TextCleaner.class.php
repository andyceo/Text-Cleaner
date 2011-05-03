<?php

class TextCleaner {
  public $text = '';
  public $stemmer_dir = '/home/andyceo/Develop/PHP-Porter-Stemmer';
  private $encoding = 'UTF-8'; // encoding of $this->text
  private $default_delimiter = ' ';
  private $stop_words_dir = __DIR__;

  /**
   * Constructor
   */
  function __construct($text = '', $encoding = 'UTF-8') {
    $this->text = $text;
    if ($encoding != $this->encoding) {
      $this->text = mb_convert_encoding($this->text, $this->encoding, $encoding);
    }
  }

  /**
   * Destructor
   */
  function __destruct() {

  }

  /**
   * Step 1.0: Remove html markup.
   */
  function html_cleanup() {
    $this->text = strip_tags($this->text);
  }

  /**
   * Step 1.1: Punctuation
   */
  function delimiters_cleanup() {
    if (empty($this->default_delimiter)) {
      $this->default_delimiter = ' ';
    }
    $default_delimiter = $this->default_delimiter;
    $delimiters = $this->get_delimiters($this->default_delimiter);
    $this->text = str_replace($delimiters, $default_delimiter, $this->text);

    // after the punctuation cleaning, we can split text to the array of words
    $this->text = explode($default_delimiter, $this->text);
    $this->text = array_filter($this->text); // remove the empty elements
  }

  /**
   * Step 1.2: Numbers
   */
  function numbers_cleanup() {
    $this->text = array_filter($this->text, function ($v) {return !is_numeric($v);});
  }

  /**
   * Step 1.3: Single chars
   */
  function single_chars_cleanup() {
    $this->text = array_filter($this->text, function ($v) {return mb_strlen($v) > 1;});
  }

  /**
   * Step 1.4: Remove the stop-list words
   */
  function stop_words_cleanup() {
    $stop_words = file_get_contents($this->stop_words_dir . '/stop_words.txt');
    $stop_words = explode("\n", $stop_words);
    $stop_words = array_map('trim', $stop_words);
    $stop_words = array_filter($stop_words);
    $this->text = array_filter($this->text, function ($v) use ($stop_words) {
      return !in_array($v, $stop_words);}
    );
  }

  /**
   * Step 1: Preliminary text cleaning, in five steps:
   *   1.1 Punctuation
   *   1.2 Numbers
   *   1.3 Single chars
   *   1.4 Remove the stop-list words
   */
  function text_cleanup() {
    $this->html_cleanup();
    $this->delimiters_cleanup();
    $this->numbers_cleanup();
    $this->single_chars_cleanup();
    $this->stop_words_cleanup();
  }

  /**
   * Step 2: stemming
   */
  function text_stemming() {
    if (empty($this->stemmer_dir)) {
      return FALSE;
    }
    require_once($this->stemmer_dir . '/Lingua_Stem_Ru.class.php');
    $stemmer = new Lingua_Stem_Ru;
    $this->text = array_map(function ($v) use ($stemmer) {
        return $stemmer->stem_word($v);
      }, $this->text);
    $this->single_chars_cleanup();
  }

  /**
   * Step 3: count number of entries of stems
   */
  function count_stems() {
    $stems = array_count_values($this->text);
    /*
    $this->text = array_filter($this->text, function ($v) {
      return $v > 1;
    });
    */
    arsort($stems);
    return $stems;
  }

  /**
   * Step 4: LSA (LSI) begin :)
   */

  /**
   *
   */
  function get_delimiters($default_delimiter = ' ') {
    $delimiters = array(' ', ' ', ',', '.', '!', '?', ';', ':', '"', '*', '~',
    ' -', '- ', '--', '&nbsp;', '(', ')', '[', ']', '{', '}', '#', '%',
    '/', '\\', '  ', '|', '+', '&',
    "'", "\n", "\r", );
    $delimiters = array_unique($delimiters);

    // убираем defaul delimiter из массива разделителей
    $pos = array_search($default_delimiter, $delimiters);
    if ($pos !== FALSE) {
      unset($delimiters[$pos]);
    }

    return $delimiters;
  }
}
