
<div class="flex-auto px-0 pt-0 pb-2">
    <div class="p-0 overflow-x-auto">
      <table class="w-96 items-center mb-0 align-top border-gray-200 text-slate-500">
        <thead class="align-bottom">
          <tr>
            <th class="px-3 py-3 leading-normal text-sm">Subject</th>
            <th class="px-3 py-3 leading-normal text-sm">Grade</th>
            <th class="px-6 py-3 font-semibold capitalize align-middle bg-transparent border-b border-gray-200 border-solid shadow-none tracking-none whitespace-nowrap text-slate-400 opacity-70"></th>
          </tr>
        </thead>
        <tbody>
            @foreach ($this->subjectGrades as $subject)
            <div wire:key="{{$subject->id}}">

          <tr>
            <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
              <div class="flex px-2 py-1">

                <div class="flex flex-col justify-center">
                  <h6 class="mb-0 leading-normal text-sm">{{$subject->subject_name}}</h6>
                  <p class="mb-0 leading-tight text-xs text-slate-400">{{$subject->exam_name}}</p>
                </div>
              </div>
            </td>
            <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
              <p class="mb-0 font-semibold leading-tight text-xs">{{$subject->grade}}</p>
              
            </td>


            <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                <button wire:click="delete({{$subject->id}})" class="text-sm text-red-500 font-semibold rounded hover:text-teal-800 mr-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                </button>
            </td>
          </tr>
        </div>
          @endforeach


        </tbody>
      </table>
    </div>
  </div>
