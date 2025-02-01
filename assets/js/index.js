function calendar() {
    const date = new Date();
    let month = date.getMonth();
    let year = date.getFullYear();


    return {
        weeks: getWeeks(month, year),
        month: month,
        year: year,
        get monthName() {
            return getMonthName(this.month);
        },
        isToday(day) {
            const today = new Date();

            const d = new Date(this.year, this.month, day);
            return today.toDateString() === d.toDateString() ? true : false;
        },
        isActive(day) {
            const currentDate = new Date();

            const d = new Date(this.year, this.month);

            const currentDay = currentDate.getDate();
            const currentMonth = currentDate.getMonth();
            const currentYear = currentDate.getFullYear();

            return (
                this.month === currentMonth &&
                this.year === currentYear &&
                day === currentDay
            );
        },
        previousMonth() {
            this.month--;
            if (this.month < 0) {
                this.month = 11;
                this.year--;
            }
            this.weeks = getWeeks(this.month, this.year);
        },
        nextMonth() {
            this.month++;
            if (this.month > 11) {
                this.month = 0;
                this.year++;
            }
            this.weeks = getWeeks(this.month, this.year);
        },
    };
}

function getMonthName(month) {
    const options = { month: "long" };
    const date = new Date();
    date.setMonth(month);
    return new Intl.DateTimeFormat("en-US", options).format(date);
}


function getWeeks(month, year) {
    const weeks = [];
    const firstDayOfMonth = new Date(year, month, 1);
    const lastDayOfMonth = new Date(year, month + 1, 0);
    const daysInMonth = lastDayOfMonth.getDate();
    const firstDayOfWeek = firstDayOfMonth.getDay();

    let week = [];

    // Fill black cells with days from previous month
    const previousMonth = month === 0 ? 11 : month - 1;
    const previousMonthYear = month === 0 ? year - 1 : year;
    const lastDayOfPreviousMonth = new Date(previousMonthYear, previousMonth + 1, 0).getDate();
    const startDay = lastDayOfPreviousMonth - firstDayOfWeek + 1;
    for (let day = startDay; day <= lastDayOfPreviousMonth; day++) {
        week.push(day);
    }

    // Fill cells with days from current month
    for (let day = 1; day <= daysInMonth; day++) {
        if (week.length === 7) {
            weeks.push(week);
            week = [];
        }
        week.push(day);
    }

    // Fill remaining cells with days from next month
    let nextMonth = month === 11 ? 0 : month + 1;
    if (week.length < 7) {
        let day = 1;
        while (week.length < 7) {
            week.push(day);
            day++;
        }
    }

    weeks.push(week);

    return weeks;
}