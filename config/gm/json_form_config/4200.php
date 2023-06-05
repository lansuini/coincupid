<?php
return [

    ["name" => "rule", "field" => "rule", "valType" => "array", "val" => [

        ["name" => "base_score", "field" => "base_score", "val" => null, "valType" => "integer", "re" => "Game score (points)"],
        ["name" => "tiyan", "field" => "tiyan", "val" => "0", "valType" => "integer", "re" => "Experience field, 1 experience, 0 non-experience"],
        ["name" => "tiyan_gold", "field" => "tiyan_gold", "val" => "100000", "valType" => "integer", "re" => "Experience Gold (Points)"],
        ["name" => "limit_play", "field" => "limit_play", "val" => "0", "valType" => "integer", "re" => "Experience field limits the number of games played"],
    ]],

    ["name" => "robot", "field" => "robot", "valType" => "arrayMuti", "val" => [
        [
            ["name" => "rate", "field" => "rate", "val" => null, "valType" => "integer"],
        ],
    ], 're' => "How many robots and probabilities per table %"],

    ["name" => "robot_leave", "field" => "robot_leave", "valType" => "arrayMuti", "val" => [
        [
            ["name" => "minute", "field" => "minute", "val" => null, "valType" => "integer", "re" => "Robot left minutes"],
            ["name" => "chance", "field" => "chance", "val" => null, "valType" => "integer", "re" => "Probability of robot leaving %"],
        ],
    ], 're' => "Probability of the robot leaving the room after playing xx time %"],

    ["name" => "robot_number", "field" => "robot_number", "valType" => "arrayMuti", "val" => [
        [
            ["name" => "number", "field" => "number", "val" => null, "valType" => "integer", "re" => "Number of robots per table"],
            ["name" => "chance", "field" => "chance", "val" => null, "valType" => "integer", "re" => "Single table robot probability%"],
        ]
    ], 're' => "How many robots and probabilities per table%"],

    ["name" => "robot_config", "field" => "robot_config", "valType" => "array", "val" => [  
        ["name" => "robot_max_table", "field" => "robot_max_table", "val" => null, "valType" => "integer", "re" => "Maximum number of robots in a room"],
        ["name" => "robot_max_gold", "field" => "robot_max_gold", "val" => null, "valType" => "integer", "re" => "The maximum gold coins that the robot can carry (cents)"],
    ], 're' => "some other bot settings"],
];