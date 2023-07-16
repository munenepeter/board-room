<?php include_once 'base.view.php'; ?>

<div class="antialiased bg-gray-50 dark:bg-gray-900">
    <!-- Nav -->
    <?php include_once 'sections/nav.view.php'; ?>
    <!-- Nav -->
    <!-- Sidebar -->
    <?php include_once 'sections/sidebar.view.php'; ?>

    <style>
        .calendar {
            display: flex;
            flex-flow: column;
        }

        .calendar .header .month-year {
            font-size: 20px;
            font-weight: bold;
            color: #636e73;
            padding: 20px 0;
        }

        .calendar .days {
            display: flex;
            flex-flow: wrap;
        }

        .calendar .days .day_name {
            width: calc(100% / 7);
            border-right: 1px solid #2c7aca;
            padding: 18px;
            text-transform: uppercase;
            font-size: 10px;
            color: #818589;
            color: #fff;
            background-color: #518fce;
        }

        .calendar .days .day_name:nth-child(7) {
            border: none;
        }

        .calendar .days .day_num {
            display: flex;
            flex-flow: column;
            width: calc(100% / 7);
            border-right: 1px solid #e6e9ea;
            border-bottom: 1px solid #e6e9ea;
            padding: 15px;
            color: #7c878d;
            cursor: pointer;
            min-height: 90px;
        }

        .calendar .days .day_num span {
            display: inline-flex;
            width: 30px;
            font-size: 12px;
        }

        /* .calendar .days .day_num .event {
            margin-top: 10px;
            font-weight: 500;
            font-size: 10px;
            padding: 3px 6px;
            border-radius: 4px;
            background-color: #f7c30d;
            color: #fff;
            word-wrap: break-word;
        } */

        .calendar .days .day_num:nth-child(7n+1) {
            border-left: 1px solid #e6e9ea;
        }

        .calendar .days .day_num:hover {
            background-color: #fdfdfd;
        }

        .calendar .days .day_num.ignore {
            background-color: #fdfdfd;
            color: #ced2d4;
            cursor: inherit;
        }

        .calendar .days .day_num.selected {
            background-color: #f1f2f3;
            cursor: inherit;
        }
    </style>
    <main class="bg-white pt-4 ml-32 my-2">
        <?php include_once 'sections/second-nav.view.php'; ?>
        <div class="container mx-auto px-1">
            <?php

            $calendar = new \BoardRoom\Core\Calendar($date->format("Y-m-d"));

            foreach ($meetings as $meeting) {
                $txt = "<p class=\"text-sm text-white\">{$meeting->name}  .  <span class=\"text-xs\">".format_time_to_minutes($meeting->duration) ."</span></p>";
                $calendar->add_event($txt, date('Y-m-d', strtotime($meeting->meeting_date)));
            }
            $calendar->add_event("Test",'2023-08-16');

            echo $calendar;
            ?>
        </div>


    </main>
</div>