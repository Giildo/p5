<?php

namespace Core\Controller;

trait InstantiationModels
{
    /**
     * @param array $models
     * @return void
     */
    public function InstantiationModels(array $models): void
    {
        // Implémente les Models nécessaires récupérés depuis la config
        if (!empty($models))
        {
            foreach ($models as $key => $model) {
                $key .= "Model";
                $this->$key = $model;
            }
        }
    }
}
