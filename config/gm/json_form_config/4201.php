<?php
return [

    ["name" => "rule", "field" => "rule", "valType" => "array", "val" => [

        ["name" => "base_score", "field" => "base_score", "val" => null, "valType" => "integer", "re" => 'Game score (points)'],
        ["name" => "shuffle_count", "field" => "shuffle_count", "val" => 3, "valType" => "integer", "re" => 'Number of cuts'],
        ["name" => "shuffle_countex", "field" => "shuffle_countex", "val" => 3, "valType" => "integer", "re" => 'Number of shuffles'],
        ["name" => "deal_count", "field" => "deal_count", "val" => null, "valType" => "integer", "re" => 'How to deal cards, how many cards are drawn from the deck at a time'],
        ["name" => "bome_from", "field" => "bome_from", "val" => null, "valType" => "integer", "re" => 'Minimum number of bombs in bomb gameplay'],
        ["name" => "bome_to", "field" => "bome_to", "val" => null, "valType" => "integer", "re" => 'The maximum number of bombs in the bomb game'],
        ["name" => "bome_rate", "field" => "bome_rate", "val" => null, "valType" => "integer", "re" => 'Probability of bomb gameplay'],
        ["name" => "straight_rate", "field" => "straight_rate", "val" => null, "valType" => "integer", "re" => 'Probability of playing a straight hand'],
        ["name" => "norand_rate", "field" => "norand_rate", "val" => null, "valType" => "integer", "re" => 'Probability of unshuffled play'],

        // ["name" => "super_switch", "field" => "super_switch", "val" => "0", "valType" => "integer", "re" => 'Super doubling switch. 0 is off, 1 is on'],
        // ["name" => "super_double", "field" => "super_double", "val" => "0", "valType" => "integer", "re" => 'super double'],

        ["name" => "play_mode", "field" => "play_mode", "val" => "0", "valType" => "integer", "re" => '0 tongits  1joker  2quick'],
        ["name" => "tag", "field" => "tag", "val" => "0", "valType" => "integer", "re" => '0 nomal  1 butasan'],

        ["name" => "tiyan", "field" => "tiyan", "val" => "0", "valType" => "integer", "re" => 'Experience field, 1 experience, 0 non-experience'],
        ["name" => "tiyan_gold", "field" => "tiyan_gold", "val" => "100000", "valType" => "integer", "re" => 'Experience Gold (Points)'],
        ["name" => "limit_play", "field" => "limit_play", "val" => "0", "valType" => "integer", "re" => 'Experience field limits the number of games played'],
        ["name" => "tax", "field" => "tax", "val" => "0", "valType" => "integer", "re" => 'Deduction ratio, percentage per thousand'],
        ["name" => "roomchargegold", "field" => "roomchargegold", "val" => "0", "valType" => "integer", "re" => ''],
    ]],

    ["name" => "robot", "field" => "robot", "valType" => "arrayMuti", "val" => [
        [
            ["name" => "rate", "field" => "rate", "val" => null, "valType" => "integer"],
        ],
    ], 're' => 'How many robots and probabilities per table %'],

    ["name" => "robot_leave", "field" => "robot_leave", "valType" => "arrayMuti", "val" => [
        [
            ["name" => "minute", "field" => "minute", "val" => null, "valType" => "integer", "re" => "Robot left minutes"],
            ["name" => "chance", "field" => "chance", "val" => null, "valType" => "integer", "re" => "Probability of robot leaving %"],
        ],
    ], 're' => 'Probability of the robot leaving the room after playing xx time %'],

    ["name" => "robot_number", "field" => "robot_number", "valType" => "arrayMuti", "val" => [
        [
            ["name" => "number", "field" => "number", "val" => null, "valType" => "integer", "re" => "Number of robots per table"],
            ["name" => "chance", "field" => "chance", "val" => null, "valType" => "integer", "re" => "Single table robot probability%"],
        ]
    ], 're' => 'How many robots and probabilities per table%'],

    ["name" => "robot_config", "field" => "robot_config", "valType" => "array", "val" => [
        ["name" => "robot_max_table", "field" => "robot_max_table", "val" => null, "valType" => "integer", "re" => "Maximum number of robots in a room"],
        ["name" => "robot_max_gold", "field" => "robot_max_gold", "val" => null, "valType" => "integer", "re" => "The maximum gold coins that the robot can carry (cents)"],
        ["name" => "robot_max_pair", "field" => "robot_max_pair", "val" => null, "valType" => "integer", "re" => "The maximum number of suitable robots, if the number is lower, add robots to the room"],
        ["name" => "precheat", "field" => "PreCheat", "val" => null, "valType" => "integer", "re" => "ACM"],
        ["name" => "patch_pool_time", "field" => "patch_pool_time", "val" => 0, "valType" => "integer", "re" => "Matching pool cooling time, in seconds, 0 seconds means not enabled"],
        ["name" => "limit_same_place", "field" => "limit_same_place", "val" => 0, "valType" => "integer", "re" => "Restrict same area 0 off 1 on"],
        ["name" => "limit_last_round", "field" => "limit_last_round", "val" => 0, "valType" => "integer", "re" => "Same table limit in the previous game: 0 off and 1 on"],
        ["name" => "limit_same_plat", "field" => "limit_same_plat", "val" => 0, "valType" => "integer", "re" => "Same platform limit 0 off 1 on"],
        ["name" => "must_robot", "field" => "must_robot", "val" => null, "valType" => "integer", "re" => "Every table must have xx robot to start"],
        ["name" => "robot_per_add", "field" => "robot_per_add", "val" => 0, "valType" => "integer", "re" => "The number of robots added to the queue per second"],
    ], 're' => 'some other bot settings'],
];
