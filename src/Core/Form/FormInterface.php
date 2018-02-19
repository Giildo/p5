<?php

namespace Core\Form;

interface FormInterface
{
    /**
     * Génère un Fieldset autour du form
     *
     * @param string $legend
     * @param null|string $class
     */
    public function fieldset(string $legend, ?string $class = ''): void;

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
        ?string $label = '',
        ?string $value = '',
        ?string $type = 'text',
        ?string $class = '',
        ?string $autocompletion = ''
    );

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
        ?string $label = '',
        ?int $rows = 10,
        ?string $value = '',
        ?string $class = ''
    );

    /**
     * Retourne un bouton de validation
     *
     * @param string $text
     * @param null|string $type
     * @param null|string $class
     * @return string
     */
    public function submit(string $text, ?string $type = 'input', ?string $class = ''): string;
}
