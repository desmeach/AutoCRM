<?php
/**
 * Created: 23.06.2023, 14:39
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace autocrm_tables\lib\controllers;

class ControllersHandler {
    /**
     * @param $entity
     * @return array|string
     */
    public static function handleController($entity): array|string {
        return match ($entity) {
            'clients' => ClientsController::class,
            'carservices' => CarservicesController::class,
            default => ['error' => 'Несуществующий контроллер'],
        };
    }
}