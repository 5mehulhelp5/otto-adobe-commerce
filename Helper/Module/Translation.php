<?php

declare(strict_types=1);

namespace M2E\Otto\Helper\Module;

class Translation
{
    private string $text = '';
    private array $placeholders = [];
    private array $values = [];
    private array $args = [];
    private string $translatedText = '';
    private array $processedPlaceholders = [];
    private array $processedArgs = [];

    /**
     * @return \Magento\Framework\Phrase|mixed|string
     */
    public function __()
    {
        $this->clear();

        $args = func_get_args();

        return $this->translate($args);
    }

    private function clear(): void
    {
        $this->text = '';
        $this->values = [];
        $this->args = [];
        $this->placeholders = [];
        $this->processedPlaceholders = [];
        $this->processedArgs = [];
        $this->translatedText = '';
    }

    /**
     * @param array $args
     *
     * @return \Magento\Framework\Phrase|mixed|string
     */
    public function translate(array $args)
    {
        $this->clear();

        $this->parseInput($args);
        $this->parsePlaceholders();

        if (count($this->placeholders) <= 0) {
            array_unshift($this->args, $this->text);

            return __(...$this->args);
        }

        $this->translatedText = (string)__($this->text);

        if (!empty($this->values)) {
            $this->replacePlaceholdersByValue();
        }

        if (!empty($this->args)) {
            $this->replacePlaceholdersByArgs();
        }

        $unprocessedArgs = array_diff($this->args, $this->processedArgs);
        if (!empty($unprocessedArgs)) {
            return $this->translatedText;
        }

        return vsprintf($this->translatedText, $unprocessedArgs);
    }

    private function parseInput(array $input): void
    {
        $this->text = (string)array_shift($input);

        if (is_array(current($input))) {
            $this->values = array_shift($input);
        }

        array_walk($input, static function (&$el) {
            if ($el === null) {
                $el = (string)$el;
            }
        });

        $this->args = $input;
    }

    private function parsePlaceholders(): void
    {
        preg_match_all('/%\w+%/', $this->text, $placeholders);
        $this->placeholders = array_unique($placeholders[0]);
    }

    private function replacePlaceholdersByValue(): void
    {
        foreach ($this->values as $placeholder => $value) {
            $newText = str_replace('%' . $placeholder . '%', $value, $this->translatedText, $count);

            if ($count <= 0) {
                continue;
            }

            $this->translatedText = $newText;
            $this->processedPlaceholders[] = '%' . $placeholder . '%';
        }
    }

    private function replacePlaceholdersByArgs(): void
    {
        $unprocessedPlaceholders = array_diff($this->placeholders, $this->processedPlaceholders);
        $unprocessedArgs = $this->args;

        foreach ($unprocessedPlaceholders as $placeholder) {
            $value = array_shift($unprocessedArgs);

            if ($value === null) {
                break;
            }

            $this->translatedText = str_replace($placeholder, $value, $this->translatedText);

            $this->processedPlaceholders[] = $placeholder;
            $this->processedArgs[] = $value;
        }
    }
}
