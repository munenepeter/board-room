<?php include_once 'base.view.php'; ?>

<div class="antialiased bg-gray-50 dark:bg-gray-900">
    <!-- Nav -->
    <?php include_once 'sections/nav.view.php'; ?>
    <!-- Nav -->

    <!-- Sidebar -->
    <?php include_once 'sections/sidebar.view.php'; ?>

    <main class="bg-white pt-4 ml-32 my-2">

        <?php include_once 'sections/second-nav.view.php'; ?>
        <article class="w-full flex justify-around my-1 pb-6 p-2 border rounded-md shadow-sm">
            <section class="w-3/4 dark:bg-gray-700 bg-white rounded-b p-2">
                <div class="overflow-y-auto max-h-[28rem]  w-full">
                    <div class="ml-1 flex flex-col">
                        <div class="flex-1">
                            <div x-data="{ time: ['12AM', '1AM', '2AM', '3AM', '4AM', '5AM', '6AM', '7AM', '8AM', '9AM', '10AM', '11AM', '12PM', '1PM', '2PM', '3PM', '4PM', '5PM', '6PM', '7PM', '8PM', '9PM', '10PM', '11PM'] }">
                                <template x-for="(hour, index) in time" :key="index">
                                    <div class="flex">
                                        <div class="border-r border-gray-50 hover:border-t h-1/24 pb-2 w-1/12 -mr-4 -mt-1 text-sm text-gray-500" x-text="hour">
                                        </div>
                                        <div class="bg-white border border-gray-200 w-11/12">
                                            <div class="hover:bg-gray-100 bg-gray-50 border-b border-gray-200 h-20"></div>
                                            <div class="hover:bg-gray-100 bg-gray-50 border-b border-gray-200 h-20"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <?php include_once 'sections/calendar.view.php'; ?>
        </article>

</div>

</main>