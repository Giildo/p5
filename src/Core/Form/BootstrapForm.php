<?php

namespace Core\Form;

class BootstrapForm extends Form implements FormInterface
{
    /**
     * @param string $legend
     * @param null|string $class
     */
    public function fieldset(string $legend, ?string $class = ' form-group'): void
    {

        parent::fieldset($legend, $class);
    }

    /**
     * Retourne un champ de type input
     *
     * @param string $name
     * @param null|string $label
     * @param null|string $value
     * @param null|string $type
     * @param null|string $class
     * @param null|string $autocompletion
     * @return void
     */
    public function input(
        string $name,
        ?string $label = null,
        ?string $value = null,
        ?string $type = 'text',
        ?string $class = null,
        ?string $autocompletion = ''
    ): void {
        $class .= ' form-control';

        $this->form .= "<div class='form-group'>";
        parent::input($name, $label, $value, $type, $class, $autocompletion);
        $this->form .= "</div>";
    }

    /**
     * Retourne un champ de type TextArea
     *
     * @param string $name
     * @param null|string $label
     * @param int|null $rows
     * @param null|string $value
     * @param null|string $class
     * @return void
     */
    public function textarea(
        string $name,
        ?string $label = null,
        ?int $rows = 10,
        ?string $value = null,
        ?string $class = null
    ): void {
        $class .= ' form-control';

        $this->form .= "<div class='form-group'>";
        parent::textarea($name, $label, $rows, $value, $class);
        $this->form .= "</div>";
    }

    /**
     * Retourne un champ de type select
     *
     * @param string $name
     * @param array $options
     * @param null|string $optionCurrent
     * @param null|string $label
     * @param null|string $class
     */
    public function select(
        string $name,
        array $options,
        ?string $optionCurrent = '',
        ?string $label = '',
        ?string $class = ''
    ): void {
        $class .= ' form-control';

        $this->form .= "<div class='form-group'>";
        parent::select($name, $options, $optionCurrent, $label, $class);
        $this->form .= "</div>";
    }

    /**
     * Retourne un bouton de validation
     *
     * @param string $text
     * @param null|string $type
     * @param null|string $class
     * @return string
     */
    public function submit(string $text, ?string $type = 'button', ?string $class = ''): string
    {
        $class .= ' btn btn-primary';

        return parent::submit($text, $type, $class, $line);
    }
}
