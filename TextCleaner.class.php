<?php

class TextCleaner {
  private $wa = array(); // array of words
  private $main_text_lang = 'ru';
  private $encoding = 'UTF-8'; // encoding of $this->text
  private $default_delimiter = ' ';
  private $stop_words_dir = __DIR__ . '/stopwords';

  /**
   * Constructor
   */
  function __construct($text = '', $encoding = 'UTF-8') {
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

  function getWordsArray() {
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
      return !in_array($v, $stop_words);}
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

    $this->wa = array_count_values($this->wa);
    arsort($this->wa);
    return TRUE;
  }

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
