<?php

return [
    'temperature' => [
        'safe_min' => 0,
        'safe_max' => 6,
        'critical_min' => 0,
        'critical_max' => 8,
        'duration_critical_minutes' => 30,
    ],
    'risk' => [
        'warning_base' => 20,
        'warning_temperature_multiplier' => 10,
        'critical_base' => 50,
        'critical_deviation_multiplier' => 5,
        'critical_temperature_cap' => 70,
        'duration_divisor' => 2,
        'duration_cap' => 30,
    ],
];
