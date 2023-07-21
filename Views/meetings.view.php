<?php include_once 'base.view.php'; ?>

<div class="antialiased bg-gray-50 dark:bg-gray-900">
    <!-- Nav -->
    <?php include_once 'sections/nav.view.php'; ?>
    <!-- Nav -->

    <!-- Sidebar -->
    <?php include_once 'sections/sidebar.view.php'; ?>


    <main class="bg-white pt-4 ml-32 my-2">
        <?php include_once 'sections/second-nav.view.php'; ?>

        <section class="bg-white md:ml-2 h-auto flex justify-between">
            <article class="w-full flex justify-around my-2 pb-4 p-2">
                <section class="max-w-xl dark:bg-gray-700 p-4 rounded-md shadow-sm border border-blue-100">
                    <h1 class="text-xl mb-2 font-bold text-blue-900">All meetings</h1>
                    <?php if (!empty($meetings)) : ?>
                        <?php foreach ($meetings as $meeting) : ?>
                            <div class="border-b py-4 border-blue-400 border-dashed">
                                <div class="flex justify-between items-center space-x-2">
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
                                     text-xs font-medium mx-2 px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300"><?= $meeting->type ?></span>
                                        <?php if ((new DateTime()) > (new DateTime($meeting->meeting_date))) : ?>
                                            <span class="text-red-600 text-xs font-medium mr-2 px-2.5 py-0.5">Meeting Expired <?= time_ago($meeting->meeting_date) ?></span>
                                        <?php endif; ?>
                                    </a>



                                    <button id="meeting_drop" data-dropdown-toggle="meeting_dropdown" type="button"> <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                        </svg></button>
                                    <!-- Dropdown menu -->
                                    <div id="meeting_dropdown" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-20 dark:bg-gray-700">
                                        <ul class="py-2 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="meeting_drop">
                                            <li>
                                                <a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Delete</a>
                                            </li>
                                        </ul>
                                    </div>


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
                    <?php else : ?>
                        <div class="border-b border-blue-400 border-dashed">
                            <p class="py-8 flex justify-start items-center space-x-2 text-xs font-light leading-3 text-gray-500 dark:text-gray-300">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg> <span class="text-lg font-medium leading-5 text-gray-500 dark:text-gray-100 mt-2">
                                    Seems like there are no meetings!
                                </span>
                            </p>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="max-w-xl dark:bg-gray-700 p-4 rounded-md shadow-sm border border-rose-100">
                    <h1 class="text-xl mb-2 font-bold text-rose-900">Board Room Stats</h1>
                    <?php if (!empty($meetings)) : ?>
                        <?php
                        $meeting_type_count = array_count_values(array_column($meetings, 'type'));


                        $meetingCounts = array_count_values(array_column($meetings, 'owner_department'));

                        $totalMeetings = array_sum($meetingCounts);
                        $largestDepartment = "";
                        $largestCount = 0;

                        foreach ($meetingCounts as $department => $count) {
                            if ($count > $largestCount) {
                                $largestDepartment = $department;
                                $largestCount = $count;
                            }
                        }

                        // Step 3: Calculate the percentage of meetings for the largest department
                        $percentageLargestDepartment = ($largestCount / $totalMeetings) * 100;

                        ?>
                        <div class="border-b py-4 border-blue-400 border-dashed">
                            <div class="flex justify-between items-center space-x-2">
                                <a tabindex="0" class="focus:outline-none text-lg font-medium leading-5 text-gray-700 dark:text-gray-100 mt-2">Total Meetings <?= count($meetings) ?>
                                    <span class=" bg-yellow-100 text-yellow-800 text-xs font-medium mx-2 px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">External (<?= $meeting_type_count['External'] ?>)</span>
                                    <span class=" bg-green-100 text-green-800 text-xs font-medium mx-2 px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">Hybrid (<?= $meeting_type_count['Hybrid'] ?>)</span>
                                    <span class=" bg-blue-100 text-blue-800 text-xs font-medium mx-2 px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">Internal (<?= $meeting_type_count['Internal'] ?>)</span>
                                </a>

                            </div>
                            <p class="py-2 flex items-center justify-start space-x-2 text-xs font-light leading-3 text-gray-500 dark:text-gray-300">

                            </p>
                            <p class="text-sm pt-2 leading-4 leading-none text-gray-600 dark:text-gray-300"><?= round($percentageLargestDepartment, 1) . "% " ?>of the meetings were booked by the <?= $largestDepartment ?> department</p>
                        </div>
                        <!-- previous month -->
                        <div class="border-b py-4 border-blue-400 border-dashed">
                            <div class="flex justify-between items-center space-x-2">
                                <a tabindex="0" class="focus:outline-none text-lg font-medium leading-5 text-gray-700 dark:text-gray-100 mt-2"><?= date_format($month_later->modify('-2 month'), 'F') ?> Meetings (0)
                                    <span class=" bg-yellow-100 text-yellow-800 text-xs font-medium mx-2 px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">External (0)</span>
                                    <span class=" bg-green-100 text-green-800 text-xs font-medium mx-2 px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">Hybrid (0)</span>
                                    <span class=" bg-blue-100 text-blue-800 text-xs font-medium mx-2 px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">Internal (0)</span>
                                </a>

                            </div>
                            <p class="py-2 flex items-center justify-start space-x-2 text-xs font-light leading-3 text-gray-500 dark:text-gray-300">

                            </p>
                            <p class="text-sm pt-2 leading-4 leading-none text-gray-600 dark:text-gray-300">No meetings were set during this time!</p>
                        </div>
                        <!-- current month -->
                        <div class="border-b py-4 border-blue-400 border-dashed">
                            <div class="flex justify-between items-center space-x-2">
                                <a tabindex="0" class="focus:outline-none text-lg font-medium leading-5 text-gray-700 dark:text-gray-100 mt-2"><?= date_format($date, "F") ?> Meetings <?= count($meetings) ?>
                                    <span class=" bg-yellow-100 text-yellow-800 text-xs font-medium mx-2 px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">External (<?= $meeting_type_count['External'] ?>)</span>
                                    <span class=" bg-green-100 text-green-800 text-xs font-medium mx-2 px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">Hybrid (<?= $meeting_type_count['Hybrid'] ?>)</span>
                                    <span class=" bg-blue-100 text-blue-800 text-xs font-medium mx-2 px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">Internal (<?= $meeting_type_count['Internal'] ?>)</span>
                                </a>

                            </div>
                            <p class="py-2 flex items-center justify-start space-x-2 text-xs font-light leading-3 text-gray-500 dark:text-gray-300">

                            </p>
                            <p class="text-sm pt-2 leading-4 leading-none text-gray-600 dark:text-gray-300"><?= round($percentageLargestDepartment, 1) . "% " ?>of the meetings were booked by the <?= $largestDepartment ?> department</p>
                        </div>

                        <!-- next month -->
                        <!-- <div class="border-b py-4 border-blue-400 border-dashed">
                            <div class="flex justify-between items-center space-x-2">
                                <a tabindex="0" class="focus:outline-none text-lg font-medium leading-5 text-gray-700 dark:text-gray-100 mt-2"><?= date_format($month_later->modify('+2 month'), 'F') ?> Meetings (0)
                                    <span class=" bg-yellow-100 text-yellow-800 text-xs font-medium mx-2 px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">External (0)</span>
                                    <span class=" bg-green-100 text-green-800 text-xs font-medium mx-2 px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">Hybrid (0)</span>
                                    <span class=" bg-blue-100 text-blue-800 text-xs font-medium mx-2 px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">Internal (0)</span>
                                </a>

                            </div>
                            <p class="py-2 flex items-center justify-start space-x-2 text-xs font-light leading-3 text-gray-500 dark:text-gray-300">

                            </p>
                            <p class="text-sm pt-2 leading-4 leading-none text-gray-600 dark:text-gray-300">90% of the meetings were booked by the IT department</p>
                        </div> -->


                    <?php else : ?>
                        <div class="border-b border-blue-400 border-dashed">
                            <p class="py-8 flex justify-start items-center space-x-2 text-xs font-light leading-3 text-gray-500 dark:text-gray-300">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg> <span class="text-lg font-medium leading-5 text-gray-500 dark:text-gray-100 mt-2">
                                    Seems like there are no meetings!
                                </span>
                            </p>
                        </div>
                    <?php endif; ?>
                </section>
        </section>

    </main>
</div>