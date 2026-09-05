<?php

return [
    'recommendation' => [
        /*
         * Thresholds are expressed in the same units as WeatherSnapshot.
         *
         * Temperature:
         *   °C
         *
         * Wind:
         *   km/h
         *
         * Precipitation:
         *   mm
         *
         * Humidity:
         *   %
         */

        'thresholds' => [
            'temperature' => [
                'freezing_max' => 0,
                'cold_max' => 8,
                'warm_min' => 25,
                'hot_min' => 30,
            ],

            'apparent_temperature' => [
                /*
                 * apparent_temperature - temperature
                 */
                'wind_chill_delta_max' => -4,
                'heat_discomfort_delta_min' => 4,
            ],

            'precipitation' => [
                'min' => 0,
            ],

            'wind' => [
                'strong_min' => 30,
                'dangerous_gust_min' => 45,
            ],

            'humidity' => [
                'high_min' => 85,
            ],
        ],

        /*
         * The first matching factor becomes the primary recommendation
         * factor.
         */
        'priority' => [
            'dangerous_gusts',
            'storm_risk',
            'low_visibility',
            'heavy_rain',
            'snow',
            'rain',
            'freezing_temperature',
            'high_temperature',
            'strong_wind',
            'wind_chill',
            'heat_discomfort',
            'cold_temperature',
            'warm_temperature',
            'high_humidity',
            'precipitation',
        ],
    ],
];