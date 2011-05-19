<?php

// @todo use the barricade method to check functions arguments

class TextCleaner {
  private $text = ''; //text to process
  private $wa = array(); // words array
  private $main_text_lang = 'ru';
  private $main_encoding = 'UTF-8'; // encoding of the $this->text
  private $default_delimiter = ' ';
  private $stop_words_dir = __DIR__;

  /**
   * Constructor
   */
  function __construct($text = '', $encoding = 'UTF-8') {
    $this->stop_words_dir = $this->stop_words_dir . '/stopwords';
    if ($encoding != $this->encoding) {
      $text = mb_convert_encoding($text, $this->encoding, $encoding);
    }
    $this->text_cleanup($text);
  }

  /**
   * Destructor
   */
  function __destruct() {

  }

  /**
   * This function reset class to default settings
   */
  function reset() {

  }

  /**
   * This function return the array of words. Array of words created,
   * when text after cleaning treated as array of words, regardless
   * of the order of the words. Text is just an array of words.
   */
  function get_words_array() {
    return $this->wa;
  }

  /**
   * Step 1.0: Remove html markup.
   */
  function html_cleanup($text) {
    $text = strip_tags($text);
    return $text;
  }

  /**
   * Step 1.1: Punctuation
   */
  function delimiters_cleanup($text) {
    if (empty($this->default_delimiter)) {
      $this->default_delimiter = ' ';
    }
    $default_delimiter = $this->default_delimiter;
    $delimiters = $this->get_delimiters($this->default_delimiter);
    $text = str_replace($delimiters, $default_delimiter, $text);

    // after the punctuation cleaning, we can split text to the array of words
    $text = explode($default_delimiter, $text);
    $text = array_filter($text); // remove the empty elements
    return $text;
  }

  /**
   * Step 1.2: Numbers
   */
  function numbers_cleanup($text) {
    $text = array_filter($text, function ($v) {return !is_numeric($v);});
    return $text;
  }

  /**
   * Step 1.3: Single chars
   */
  function single_chars_cleanup($text) {
    $text = array_filter($text, function ($v) {return mb_strlen($v) > 1;});
    return $text;
  }

  /**
   * Step 1.4: Remove the stop-list words
   */
  function stop_words_cleanup($text) {
    $stop_words = file_get_contents($this->stop_words_dir . '/' . $this->main_text_lang . '.txt');
    $stop_words = explode("\n", $stop_words);
    $stop_words = array_map('trim', $stop_words);
    $stop_words = array_filter($stop_words);
    $text = array_filter($text, function ($v) use ($stop_words) {
      return !in_array(mb_strtolower($v), $stop_words);}
    );
    return $text;
  }

  /**
   * Preliminary text cleaning, in five steps:
   *   1.1 Punctuation
   *   1.2 Numbers
   *   1.3 Single chars
   *   1.4 Remove the stop-list words
   */
  function text_cleanup($text) {
    $text = $this->html_cleanup($text);
    $this->wa = $this->delimiters_cleanup($text);
    unset ($text); // memory!
    $this->wa = $this->numbers_cleanup($this->wa);
    $this->wa = $this->single_chars_cleanup($this->wa);
    $this->wa = $this->stop_words_cleanup($this->wa);
    $this->wa = array_values($this->wa); //reindex the array;
    return TRUE;
  }

  function count_words() {
    $counted_words = array_count_values($this->wa);
    arsort($counted_words);
    return $counted_words;
  }

  /**
   * Return array of the possible text delimiters
   */
  public static function get_delimiters($default_delimiter = ' ') {
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
