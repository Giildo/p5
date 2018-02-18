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

        parent::fieldset($legend, $class, $line);
    }

    /**
     * Retourne un champ de type input
     *
     * @param string $name
     * @param null|string $label
     * @param null|string $value
     * @param null|string $type
     * @param null|string $class
     */
    public function input(
        string $name,
        ?string $label = null,
        ?string $value = null,
        ?string $type = 'text',
        ?string $class = null
    )
    {
        $class .= ' form-control';

        $this->form .= "<div class='form-group'>";
        parent::input($name, $label, $value, $type, $class);
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
     */
    public function textarea(
        string $name,
        ?string $label = null,
        ?int $rows = 10,
        ?string $value = null,
        ?string $class = null
    )
    {
        $class .= ' form-control';

        $this->form .= "<div class='form-group'>";
        parent::textarea($name, $label, $rows, $value, $class);
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