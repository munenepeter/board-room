<?php include_once 'base.view.php'; ?>

<div class="antialiased bg-gray-50 dark:bg-gray-900">
    <!-- Nav -->
    <?php include_once 'sections/nav.view.php'; ?>
    <!-- Nav -->
    <!-- Sidebar -->
    <?php include_once 'sections/sidebar.view.php'; ?>

    <style>
         

        .calendar .days .day_name {
            width: calc(100% / 7);
            padding: 4px;
            text-align: center;
        }

        .calendar .days .day_name:nth-child(7) {
            border-left: none;
        }
       
        .calendar .days .day_num {
           
            width: calc(100% / 7);
            border-right: 1px solid #e6e9ea;
            border-bottom: 1px solid #e6e9ea;
            padding: 10px;
            cursor: pointer;
            min-height: 80px;
        }

        .calendar .days .day_num span {
            display: inline-flex;
            width: 30px;
        }

        .calendar .days .day_num:nth-child(7n+1) {
            border-left: 1px solid #e6e9ea;
        }

        
    </style>
    <main class="bg-white pt-4 ml-32 my-2">
        <?php include_once 'sections/second-nav.view.php'; ?>
        <div class="container mx-auto px-1">
            <?php

            $calendar = new \BoardRoom\Core\Calendar($date->format("Y-m-d"));

            foreach ($meetings as $meeting) {
                $txt = "<p class=\"text-sm font-bold\">{$meeting->name}  .  <span class=\"text-xs\">" . get_time($meeting->meeting_date)."</span></p>";
                $calendar->add_event($txt, date('Y-m-d', strtotime($meeting->meeting_date)));
            }
            echo $calendar;
            ?>
        </div>


    </main>
</div>