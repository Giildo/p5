<?php

namespace Core\Form;

class Form implements FormInterface
{
    /**
     * @var string Stocke le html au fur et à mesure de sa création
     */
    protected $form;

    /** @var bool Stocke s'il y a un fieldset */
    protected $fieldset = false;

    /**
     * Form constructor.
     * @param null|string $class
     * @param null|string $method
     * @param null|string $action
     */
    public function __construct(?string $class = '', ?string $method = 'POST', ?string $action = '')
    {
        $this->form = "<form action='{$action}' method='{$method}' class='{$class}'>";
    }

    /**
     * Génère un Fieldset autour du form
     *
     * @param string $legend
     * @param null|string $class
     */
    public function fieldset(string $legend, ?string $class = ''): void
    {
        $this->fieldset = true;

        $this->form .= "<fieldset class='{$class}'>" .
            "<legend>{$legend}</legend>";
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
        ?string $label = '',
        ?string $value = '',
        ?string $type = 'text',
        ?string $class = '',
        ?string $autocompletion = ''
    ): void {
        $label = $this->labelConstruct($name, $label);

        if ($type !== 'hidden') {
            $this->form .= "<label for='{$name}'>{$label}</label>";
        }

        $this->form .=
            '<input
                type="' . $type . '"
                id="' . $name . '"
                name="' . $name . '"
                value="' . $value . '"
                class="' . $class . '"
                autocomplete="' . $autocompletion . '"
            />';
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
        ?string $label = '',
        ?int $rows = 10,
        ?string $value = '',
        ?string $class = ''
    ): void {
        $label = $this->labelConstruct($name, $label);

        $this->form .= "<label for='{$name}'>{$label}</label>" .
            "<textarea id='{$name}' name='{$name}' rows='{$rows}' class='{$class}'>{$value}</textarea>";
    }

    /**
     * Insère un élément HTML perso si nécessaire
     *
     * @param string $item
     */
    public function item(string $item): void
    {
        $this->form .= $item;
    }

    /**
     * Crée un élément de type select
     *
     * @param string $name
     * @param array $options
     * @param null|string $optionCurrent
     * @param null|string $label
     * @param null|string $class
     * @return void
     */
    public function select(
        string $name,
        array $options,
        ?string $optionCurrent = '',
        ?string $label = '',
        ?string $class = ''
    ): void {
        $selected = '';

        $label = $this->labelConstruct($name, $label);

        $this->form .= "<label for='{$name}'>{$label}</label>" .
            "<select class='{$class}' name='{$name}' id='{$name}'>";

        foreach ($options as $option) {
            $optionMaj = ucfirst($option);

            if ($optionMaj === $optionCurrent) {
                $selected = 'selected';
            }

            $this->form .= "<option value='{$option}' {$selected}>{$optionMaj}</option>";

            $selected = '';
        }

        $this->form .= '</select>';
    }

    /**
     * Retourne un bouton de validation
     *
     * @param string $text
     * @param null|string $type
     * @param null|string $class
     * @return string
     */
    public function submit(string $text, ?string $type = 'input', ?string $class = ''): string
    {
        $text = ucfirst($text);

        if ($type === 'button') {
            $this->form .= "<button type='submit' class='{$class}'>{$text}</button>";
        } else {
            $this->form .= "<input type='submit' value='{$text}' class='{$class}' />";
        }

        if ($this->fieldset) {
            $this->form .= "</fieldset>";
        }

        $this->form .= "</form>";

        return $this->form;
    }

    /**
     * Vérifie si le label et le nom du champ sont les mêmes et renvoie le label dans la bonne forme
     *
     * @param string $name
     * @param null|string $label
     * @return string
     */
    protected function labelConstruct(string $name, ?string $label = ''): string
    {
        if ($label === null) {
            return ucfirst($name);
        } else {
            return ucfirst($label);
        }
    }
}
