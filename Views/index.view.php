<?php include_once 'base.view.php'; ?>

<div class="antialiased bg-gray-50 dark:bg-gray-900">
    <!-- Nav -->
    <?php include_once 'sections/nav.view.php'; ?>
    <!-- Nav -->

    <!-- Sidebar -->
    <?php include_once 'sections/sidebar.view.php'; ?>
    <!-- Side bar -->


    <main class="p-4 md:ml-32 h-auto pt-20 flex justify-between">
        <style>
            [x-cloak] {
                display: none;
            }
        </style>

        <section x-data="calendar()" class="w-full rounded-md border">
            <div class="flex justify-between bg-gray-100 p-4 rounded-md">
                <p><span class="font-semibold" x-text="`${getMonthName(month)}, ${year}`">January 22,
                        2022</span><br><span class="text-sm text-gray-400">Saturday</span></p>
                <div class="flex items-center justify-end space-x-4">
                    <div class="text-center flex justify-between">
                        <button class="mr-2" x-on:click="previousMonth"><svg xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                        </button>
                        <h2 class="text-md font-bold">Today</h2>
                        <button class="ml-2" x-on:click="nextMonth"><svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                    </div>
                    <span>|</span>


                    <!-- Modal toggle -->
                    <div class="flex justify-center m-5">
                        <button id="defaultModalButton" data-modal-toggle="defaultModal"
                            class="block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                            type="button">
                            Book
                        </button>
                    </div>

                    <!-- Main modal -->
                    <div id="defaultModal" tabindex="-1" aria-hidden="true"
                        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full">
                        <div class="relative p-4 w-full max-w-2xl h-full md:h-auto">
                            <!-- Modal content -->
                            <div class="relative p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
                                <!-- Modal header -->
                                <div
                                    class="flex justify-between items-center pb-4 mb-4 rounded-t border-b sm:mb-5 dark:border-gray-600">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        Schedule for a new Event
                                    </h3>
                                    <button type="button"
                                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white"
                                        data-modal-toggle="defaultModal">
                                        <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="sr-only">Close modal</span>
                                    </button>
                                </div>
                                <!-- Modal body -->
                                <form action="/events/book" method="post">
                                    <div class="grid gap-4 mb-4 sm:grid-cols-2">
                                        <div>
                                            <label for="name"
                                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Event Name</label>
                                            <input type="text" name="name" id="name"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="Type an event name" required="">
                                        </div>
                                        <div>
                                            <label for="event_type"
                                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Event Type</label>
                                            <select id="event_type"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                                <option selected="">Select Event Type</option>
                                                <option value="internal">Internal</option>
                                                <option value="external">External</option>
                                                <option value="hybrid">Hybrid</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="start_time"
                                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Start Time</label>
                                            <input type="time" name="start_time" id="start_time"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="08:00" required="">
                                        </div>
                                        <div>
                                            <label for="duration"
                                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Duration</label>
                                            <input type="text" name="duration" id="duration" required pattern="[0-9]{2}:[0-9]{2}:[0-9]{2}:[0-9]{2}" title="Write a duration in the format hh:mm:ss:ms"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="hh:mm:ss:ms">
                                        </div>
                                      
                                       
                                        <div class="sm:col-span-2">
                                            <label for="description"
                                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Description</label>
                                            <textarea id="description" rows="4"
                                                class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="Write the event description here"></textarea>
                                        </div>
                                    </div>
                                    <button type="submit"
                                        class="text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                        <svg class="mr-1 -ml-1 w-6 h-6" fill="currentColor" viewBox="0 0 20 20"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        Book New Event
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
            <!-- End of NAV -->
            <article class="pt-4 bg-white flex">
                <div class="overflow-y-auto max-h-96  w-3/4">
                    <div class="ml-1 flex flex-col">
                        <div class="flex-1">
                            <div
                                x-data="{ time: ['12AM', '1AM', '2AM', '3AM', '4AM', '5AM', '6AM', '7AM', '8AM', '9AM', '10AM', '11AM', '12PM', '1PM', '2PM', '3PM', '4PM', '5PM', '6PM', '7PM', '8PM', '9PM', '10PM', '11PM'] }">
                                <template x-for="(hour, index) in time" :key="index">
                                    <div class="flex">
                                        <div class="border-r border-gray-50 h-1/24 pb-2 w-1/12 -mr-4 -mt-1"
                                            x-text="hour"></div>
                                        <div class="bg-white border border-gray-100 w-10/12">
                                            <div class="hover:bg-gray-100 border-b border-gray-100 h-10"></div>
                                            <div class="hover:bg-gray-100 border-b border-gray-100 h-10"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End of daily -->
                <section class="-ml-12 w-1/4 bg-white p-1">
                    <div>
                        <div class="mb-4 text-center flex justify-between">
                            <button class="mr-2" x-on:click="previousMonth"><svg xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                    class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 19.5L8.25 12l7.5-7.5" />
                                </svg>
                            </button>
                            <h2 class="text-md font-semibold" x-text="`${getMonthName(month)} ${year}`"></h2>
                            <button class="ml-2" x-on:click="nextMonth"><svg xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                    class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>
                        </div>

                        <section class="calendar">
                            <div class="grid grid-cols-7 text-center text-gray-400">
                                <p>S</p>
                                <p>M</p>
                                <p>T</p>
                                <p>W</p>
                                <p>T</p>
                                <p>F</p>
                                <p>S</p>
                            </div>
                            <div class="mt-1 rounded-md border bg-gray-50 text-center">
                                <template x-for="week in weeks">
                                    <div class="flex flex flex-wrap justify-evenly items-center text-center ">
                                        <template x-for="day in week">
                                            <form action="/events" method="get"
                                                :class="{'bg-blue-500 text-white text-center w-[14.2857%] px-3 py-2': isToday(day) == true, 'w-[14.2857%] px-3 py-2 hover:bg-blue-200 text-center border-r border-b': isToday(day) == false }">
                                                <input type="hidden" name="intent" value="checkEvent">
                                                <input type="hidden" name="date"
                                                    :value="`${day} ${getMonthName(month)} ${year}`">
                                                <button role="button" type="submit" class="text-center" x-text="day">
                                                </button>
                                            </form>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </section>
                    </div>

                </section>
            </article>



        </section>

    </main>
</div>
