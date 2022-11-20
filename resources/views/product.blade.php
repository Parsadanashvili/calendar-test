@extends('layouts.app')

@section('content')
    <div
        class="relative flex items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center py-4 sm:pt-0">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="text-align-center">
                <a href="{{ route('home') }}" class="text-gray-600 dark:text-gray-300">
                    Go back
                </a>
            </div>

            <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center">
                        <div class="ml-2 text-lg leading-7 font-semibold">
                            <a href="#" class="underline text-gray-900 dark:text-white">
                                {{ $product->title }}
                            </a>
                        </div>
                    </div>

                    <div class="ml-2">
                        <div class="mt-2 text-gray-600 dark:text-gray-400 text-sm">
                            {{ $product->description }}
                        </div>
                    </div>
                </div>

                <div class="p-6 flex justify-between">
                    <div class="ml-2 text-gray-600 dark:text-gray-300 text-xl">Price:</div>
                    <div class="ml-2 text-blue-800 dark:text-blue-400 text-lg">{{ $product->price }} ₾</div>
                </div>
            </div>

            <div class="mt-8 p-8 bg-white dark:bg-gray-800 overflow-hidden shadow sm:rounded-lg" x-data="{
                month: 9,
                selectedDate: '',
                productId: {{ $product->id }}
            }">
                <div class="flex flex-col border border-gray-600 py-4 rounded-lg">
                    <div class="grid grid-cols-7 gap-4 text-gray-300 pb-4 border-b border-gray-600">
                        <div class="m-auto">Mon</div>
                        <div class="m-auto">Tue</div>
                        <div class="m-auto">Wed</div>
                        <div class="m-auto">Thu</div>
                        <div class="m-auto">Fri</div>
                        <div class="m-auto">Sat</div>
                        <div class="m-auto">Sun</div>
                    </div>

                    <div class="grid grid-cols-7 gap-4 text-gray-300 pt-4" id="calendar">

                    </div>
                </div>

                <div class="mt-8 p-4 bg-white dark:bg-gray-700 overflow-hidden shadow rounded-lg" id="timeSelector"
                    style="display: none">
                    <input type="hidden" id="selectedDate" />

                    <div class="mb-3 w-full">
                        <label for="time_start_timestart"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">Start time</label>
                        <input type="time"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            id="start_time" placeholder="Start time" step="30" />
                    </div>

                    <div class="mb-3 w-full">
                        <label for="end_time" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">End
                            time</label>
                        <input type="time"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            id="end_time" placeholder="End time" step="30" />
                    </div>

                    <div class="w-full">
                        <button id="validateBtn"
                            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 w-full">Validate</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <template id="calendarCell">
        <div class="m-auto w-[60px] h-[60px] flex items-center justify-center">

        </div>
    </template>

    <script>
        class Calendar {
            constructor() {
                this.cells = [];
                this.activeCells = [];
                this.currentMonth = moment().locale('ka');
                this.selectedDate = null;
                this.element = $('#calendar');

                this.setCalendarCells()
            }

            async setCalendarCells() {
                await this.getSchedules();
                this.cells = this.generateCalendarDates(this.currentMonth);

                if (this.cells === null) {
                    console.error('Can\'t generate cells for calendar');
                    return;
                }

                $(this.element).html('');

                let selectableClass =
                    'bg-gray-900 hover:bg-blue-600 ease-in-out duration-300 rounded-lg cursor-pointer calendar-date';
                let disabledClass = 'bg-gray-700 text-gray-600 rounded-lg';


                for (let i = 0; i < this.cells.length; i++) {
                    let template = $($.parseHTML($('#calendarCell').html()));

                    if (this.cells[i].disabled || !this.cells[i].isInCurrentMonth) {
                        template.addClass(disabledClass)
                    } else {
                        template.addClass(selectableClass)
                    }

                    template.attr('data-date', this.cells[i].date.format('YYYY-MM-DD'))

                    template.html(this.cells[i].date.date())

                    $(this.element).append(template);
                }

                this.addEventListenersToCells()
            }

            generateCalendarDates(monthToShow = moment()) {
                if (!moment.isMoment(monthToShow)) return null;

                let dateStart = moment(monthToShow).startOf('month');
                let dateEnd = moment(monthToShow).endOf('month');
                let cells = [];

                while (dateStart.day() !== 1) {
                    dateStart.subtract(1, 'days')
                }

                while (dateEnd.day() !== 0) {
                    dateEnd.add(1, 'days')
                }

                do {
                    let activeCell = this.activeCells.find(
                        (cell) =>
                        moment(cell.day_date).format("YYYY-MM-DD") === dateStart.format("YYYY-MM-DD")
                    );

                    cells.push({
                        date: moment(dateStart),
                        isInCurrentMonth: dateStart.month() === monthToShow.month(),
                        disabled: ((dateStart.format('YYYY-MM-DD') < moment().format('YYYY-MM-DD')) || (
                            dateStart.format('YYYY-MM-DD') < moment().add(12, 'hours').format(
                                'YYYY-MM-DD')) || !activeCell)
                    })

                    dateStart.add(1, 'days');
                } while (dateStart.isSameOrBefore(dateEnd));

                return cells
            }

            async getSchedules() {
                let date = this.currentMonth;
                let month = String(date.format('M')).padStart(2, '0');

                try {
                    await $.get("{{ route('schedule', $product->id) }}", {
                        month: month,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    }).then((res) => {
                        this.activeCells = res;
                    });
                } catch (error) {}
            }

            addEventListenersToCells() {
                $('.calendar-date').on('click', (e) => {
                    let element = $(e.target);

                    this.selectedDate = element.attr('data-date');

                    $('#selectedDate').val(element.attr('data-date'))

                    $('#timeSelector').show()

                    let prevElement = $('.calendar-date.bg-blue-600');
                    if (prevElement) {
                        prevElement.removeClass('bg-blue-600')
                        prevElement.addClass('bg-gray-900')
                    }

                    element.removeClass('bg-gray-900')
                    element.addClass('bg-blue-600')
                })
            }
        }
    </script>

    <script>
        new Calendar()

        $('#validateBtn').click((e) => {
            let date = $('#selectedDate').val();
            let start_time = $('#start_time').val();
            let end_time = $('#end_time').val();

            $.post("{{ route('schedule.validate', $product->id) }}", {
                date,
                start_time,
                end_time,
                _token: "{{ csrf_token() }}"
            }).then((res) => {
                if (res?.available) {
                    alert('You booked successfully')
                } else if (res?.available == 0) {
                    alert('This venue is already booked at this time')
                } else {
                    alert('Something went wrong!')
                }
            })
        });
    </script>
@endsection
