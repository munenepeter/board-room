<?php

namespace BoardRoom\Core;


class Calendar {

    private $active_year, $active_month, $active_day;
    private $events = [];

    public function __construct(string $date = null) {
        $this->active_year = $date != null ? date('Y', strtotime($date)) : date('Y');
        $this->active_month = $date != null ? date('m', strtotime($date)) : date('m');
        $this->active_day = $date != null ? date('d', strtotime($date)) : date('d');
    }

    public function add_event(string $txt, $date, int $days = 1, $color = '') {
        $color = $color ? ' ' . $color : $color;
        $this->events[] = [$txt, $date, $days, $color];
    }

    public function __toString() {
        $num_days = date('t', strtotime($this->active_day . '-' . $this->active_month . '-' . $this->active_year));
        $num_days_last_month = date('j', strtotime('last day of previous month', strtotime($this->active_day . '-' . $this->active_month . '-' . $this->active_year)));
        $days = [0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];
        $first_day_of_week = array_search(date('D', strtotime($this->active_year . '-' . $this->active_month . '-1')), $days);
        $html = '<div class="calendar flex flex-col">';
        $html .= '<div class="days flex flex-wrap">';
        foreach ($days as $day) {
            $html .= '
                <div class="day_name border border-gray-300 font-bold text-sm">
                    ' . $day . '
                </div>
            ';
        }
        for ($i = $first_day_of_week; $i > 0; $i--) {
            $html .= '
                <div class="day_num flex flex-col bg-gray-100 text-gray-500">
                    ' . ($num_days_last_month - $i + 1) . '
                </div>
            ';
        }
        for ($i = 1; $i <= $num_days; $i++) {
            $selected = '';
            if ($i == $this->active_day) {
                $selected = 'bg-blue-700 p-1 rounded-full text-white';
            }
            $html .= '<div class="day_num flex flex-col hover:bg-gray-200">';
            $html .= '<a href="/meetings/view?intent=check_event&date='.urlencode($i . '-' . $this->active_month . '-' . $this->active_year).'" class="block ' . $selected . '">' . $i . '</a>';
            foreach ($this->events as $event) {
                for ($d = 0; $d <= ($event[2] - 1); $d++) {
                    if (date('y-m-d', strtotime($this->active_year . '-' . $this->active_month . '-' . $i . ' -' . $d . ' day')) == date('y-m-d', strtotime($event[1]))) {
                        $html .= '<a href="/meetings/view?intent=view_event&date='.urlencode($event[2]).'" class="text-sm" style="color:'. getRandColor(). '">';
                        $html .= $event[0];
                        $html .= '</a>';
                    }
                }
            }
            $html .= '</div>';
        }
        for ($i = 1; $i <= (42 - $num_days - max($first_day_of_week, 0)); $i++) {
            $html .= '
                <div class="day_num flex flex-col bg-gray-100 text-gray-500">
                    ' . $i . '
                </div>
            ';
        }
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }
}
