<?php include_once 'base.view.php'; ?>

<div class="antialiased bg-gray-50 dark:bg-gray-900">
    <!-- Nav -->
    <?php include_once 'sections/nav.view.php'; ?>
    <!-- Nav -->

    <!-- Sidebar -->
    <?php include_once 'sections/sidebar.view.php'; ?>


    <main class="bg-white pt-4 ml-32 my-2">


        <?php include_once 'sections/second-nav.view.php'; ?>

        <section class="bg-white p-4 md:ml-20 my-4 h-auto flex justify-around">
            <article class="w-full flex justify-around my-2 pb-6 p-2 border rounded-md shadow">
                <section class="max-w-xl dark:bg-gray-700 bg-gray-50 rounded-b p-4 rounded-md">
                    <h1 class="text-xl mb-4 font-bold">Upcoming meetings</h1>
                    <?php foreach ($meetings as $meeting) : ?>
                        <div class="border-b py-4 border-gray-400 border-dashed">
                            <div class="flex justify-between items-center">
                                <a tabindex="0" class="focus:outline-none text-lg font-medium leading-5 text-gray-700 dark:text-gray-100 mt-2"><?= $meeting->name ?>
                                    <span class="
                                    <?php
                                    if (strtolower($meeting->type) === "external") {
                                        echo 'bg-yellow-100 text-yellow-800';
                                    } elseif (strtolower($meeting->type) === "hybrid") {
                                        echo 'bg-green-100 text-green-800';
                                    } else {
                                        echo 'bg-blue-100 text-blue-800';
                                    }
                                    ?>
                                     text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300"><?= $meeting->type ?></span>
                                </a>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                </svg>
                            </div>
                            <p class="py-2 flex items-center justify-start space-x-2 text-xs font-light leading-3 text-gray-500 dark:text-gray-300">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg> <span>
                                    <?= format_meeting_date($meeting->meeting_date) ?> for
                                    <?= format_time_to_minutes($meeting->duration) ?> | Booked by
                                    <?= ucwords(str_replace("_", " ", $meeting->owner)) ?> on
                                    <?= date("F jS, Y", strtotime($meeting->created_at)) ?>
                                </span>
                            </p>
                            <p class="text-sm pt-2 leading-4 leading-none text-gray-600 dark:text-gray-300"></p>
                        </div>
                    <?php endforeach; ?>
                </section>
                <?php include_once 'sections/calendar.view.php'; ?>
        </section>

    </main>
</div>