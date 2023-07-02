<?php include_once 'base.view.php'; ?>

<div class="antialiased bg-gray-50 dark:bg-gray-900">
    <!-- Nav -->
    <?php include_once 'sections/nav.view.php'; ?>
    <!-- Nav -->

    <!-- Sidebar -->
    <?php include_once 'sections/sidebar.view.php'; ?>

    <main class="bg-white p-4 md:ml-20 h-auto pt-20 flex justify-around">
        <style>
            [x-cloak] {
                display: none;
            }
        </style>
        <div class="max-w-xl dark:bg-gray-700 bg-white rounded-b">
            <div class="px-2">
                <h1 class="text-xl mb-4 font-bold">Upcoming meetings</h1>
                <?php for ($i = 0; $i < 3; $i++): ?>
                    <div class="border-b py-4 border-gray-400 border-dashed">
                        <div class="flex justify-between items-center">
                            <a tabindex="0"
                                class="focus:outline-none text-lg font-medium leading-5 text-gray-700 dark:text-gray-100 mt-2">Zoom
                                call with the legal team</a>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                            </svg>
                        </div>
                        <p
                            class="py-2 flex items-center justify-start space-x-2 text-xs font-light leading-3 text-gray-500 dark:text-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg> <span>July 10th, 2023 at 9:00 AM for 30mins | Booked by Mwaruguru on 27th June
                                2023</span>
                        </p>
                        <p class="text-sm pt-2 leading-4 leading-none text-gray-600 dark:text-gray-300">Discussion on UX
                            sprint and Wireframe review</p>
                    </div>
                <?php endfor; ?>

            </div>
        </div>
        <div x-data="calendar()" class="max-w-sm w-full">
            <div class="md:p-8 p-5 dark:bg-gray-800 bg-white rounded-t">
                <div class="px-4 flex items-center justify-between">
                    <span tabindex="0" class="focus:outline-none  text-base font-bold dark:text-gray-100 text-gray-800"
                        x-text="`${getMonthName(month)}, ${year}`">October
                        2020</span>
                    <div class="flex items-center">
                        <button aria-label="calendar backward" x-on:click="previousMonth"
                            class="focus:text-gray-400 hover:text-gray-400 text-gray-800 dark:text-gray-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-chevron-left"
                                width="24" height="24" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <polyline points="15 6 9 12 15 18" />
                            </svg>
                        </button>
                        <button aria-label="calendar forward" x-on:click="nextMonth"
                            class="focus:text-gray-400 hover:text-gray-400 ml-3 text-gray-800 dark:text-gray-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler  icon-tabler-chevron-right"
                                width="24" height="24" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <polyline points="9 6 15 12 9 18" />
                            </svg>
                        </button>

                    </div>
                </div>
                <div class="flex items-center justify-between pt-12 overflow-x-auto ">
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th>
                                    <div class="w-full flex justify-center">
                                        <p class="text-base font-medium text-center text-gray-800 dark:text-gray-100">
                                            Mo</p>
                                    </div>
                                </th>
                                <th>
                                    <div class="w-full flex justify-center">
                                        <p class="text-base font-medium text-center text-gray-800 dark:text-gray-100">
                                            Tu</p>
                                    </div>
                                </th>
                                <th>
                                    <div class="w-full flex justify-center">
                                        <p class="text-base font-medium text-center text-gray-800 dark:text-gray-100">
                                            We</p>
                                    </div>
                                </th>
                                <th>
                                    <div class="w-full flex justify-center">
                                        <p class="text-base font-medium text-center text-gray-800 dark:text-gray-100">
                                            Th</p>
                                    </div>
                                </th>
                                <th>
                                    <div class="w-full flex justify-center">
                                        <p class="text-base font-medium text-center text-gray-800 dark:text-gray-100">
                                            Fr</p>
                                    </div>
                                </th>
                                <th>
                                    <div class="w-full flex justify-center">
                                        <p class="text-base font-medium text-center text-gray-800 dark:text-gray-100">
                                            Sa</p>
                                    </div>
                                </th>
                                <th>
                                    <div class="w-full flex justify-center">
                                        <p class="text-base font-medium text-center text-gray-800 dark:text-gray-100">
                                            Su</p>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="week in weeks">
                                <tr>
                                    <template x-for="day in week">
                                        <td :class="{'bg-blue-500 text-white border rounded-md hover:bg-blue-400': isActive(day) == true, 'border rounded-md hover:bg-gray-200': isActive(day) == false }" class="border rounded-md">
                                            <div class="px-2 py-2 cursor-pointer flex w-full justify-center">
                                                <p class="text-base text-gray-800 dark:text-gray-100 font-medium"
                                                    x-text="day">5</p>
                                            </div>
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>


        </div>
</div>

</main>