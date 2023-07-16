<div x-data="calendar()" class="max-w-lg w-full rounded-md ">
    <div class="md:p-4 p-2 dark:bg-gray-800 bg-white rounded-t">
        <div class="px-2 flex items-center justify-between">
            <span tabindex="0" class="focus:outline-none  text-base font-bold dark:text-gray-100 text-gray-800" x-text="`${getMonthName(month)}, ${year}`">October
                2020</span>
            <div class="flex items-center">
                <button aria-label="calendar backward" x-on:click="previousMonth" class="focus:text-gray-400 hover:text-gray-400 text-gray-800 dark:text-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-chevron-left" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <polyline points="15 6 9 12 15 18" />
                    </svg>
                </button>
                <button aria-label="calendar forward" x-on:click="nextMonth" class="focus:text-gray-400 hover:text-gray-400 ml-3 text-gray-800 dark:text-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler  icon-tabler-chevron-right" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <polyline points="9 6 15 12 9 18" />
                    </svg>
                </button>

            </div>
        </div>
        <div class="flex items-center justify-between pt-6 overflow-x-auto ">
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
                <tbody class="shadow-md">
                    <template x-for="week in weeks">
                        <tr>
                            <template x-for="day in week">
                                <td :class="{'bg-blue-300 text-white border border-blue-100 hover:bg-blue-400': isActive(day) == true, 'border border-blue-100 hover:bg-gray-200': isActive(day) == false }" class="border rounded-md">
                                    <div class="px-3 py-3 cursor-pointer flex w-full justify-center">
                                        <a :href="'/meetings/view?intent=check_event&date='+`${day} ${getMonthName(month)} ${year}`" class="text-base text-gray-800 dark:text-gray-100 font-medium" x-text="day">5</a>
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