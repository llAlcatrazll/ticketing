@php
    use Carbon\Carbon;
    use App\Enums\Feedback;
@endphp

<section>
    <div class="w-[8.5in] min-h-[13in] mx-auto">
        <div class="flex justify-between align-baseline">
            <div class="opacity-50">ENGLISH VERSION</div>
            <div><span>Control No: </span><span class="inline-block min-w-[120px] border-b-[1px] border-black">&nbsp;</span></div>
        </div>
        <br>
        <div class="grid grid-cols-5 leading-none text-center">
            <img class="w-10 place-self-end col-start-2 grid-end-3 mx-2" src= "{{$logoSrc}}" alt="Province Logo"/>
            <div class="self-center">
                <div>Republic of the Philippines</div>
                <div><strong>Province of Davao del Sur</strong></div>
                <div>Matti, Digos City</div>
            </div>
        </div>
        <br>
        <h2 class="text-center text-[11pt] font-bold">HELP US SERVE YOU BETTER!</h2>
        <br>
        <p class="text-justify">
          This Client Satisfaction Measurement (CSM) tracks the customer experience of government offices. Your feedback on your recently
          concluded transaction will help this office provide a better service. Personal information shared will be kept confidential and you always have
          the option to not answer this form.
        </p>
        <br>
        <div class="flex gap-8">
            <span>Client type:</span>
            <label><input type="radio" class="radio" name="client_type" value="Citizen" {{ $record->client_type == 'citizen' ? 'checked' : '' }} disabled> Citizen</label>
            <label><input type="radio" class="radio" name="client_type" value="Business" {{ $record->client_type == 'business' ? 'checked' : '' }} disabled> Business</label>
            <label><input type="radio" class="radio" name="client_type" value="Government" {{ $record->client_type == 'government' ? 'checked' : '' }} disabled> Government (Employee or another agency)</label>
        </div>
        <div class="flex gap-10 mt-2">
            <div><span>Date:</span> <span class="inline-block min-w-[80px] border-b-[1px] border-black">{{Carbon::parse($record->created_at)->format('F j, Y')}}</span></div>
            <div><span>Gender:</span>
              <label><input type="radio" class="radio" name="gender" value="Male" {{ $record->gender == 'male' ? 'checked' : '' }} disabled> Male</label>
              <label><input type="radio" class="radio" name="gender" value="Female" {{ $record->gender == 'female' ? 'checked' : '' }} disabled> Female</label>
            </div>
            <div><span>Age:</span> <span class="inline-block min-w-[80px] border-b-[1px] border-black">{{$record->age}}</span></div>
            <div><span>Region of residence:</span> <span class="inline-block min-w-[120px] border-b-[1px] border-black">{{$record->residence}}</span></div>
        </div>
        <br>
        <div>
          <div>Services availed:</div>
          <div class="border border-black grid grid-cols-2 divide-x divide-black">
            <div class="p-2">
              <div class="font-bold">External Services</div>
              <ul class="ml-4">
                @foreach ($record->organization->categories as $category)
                    @if(in_array(Feedback::EXTERNAL->value, $category->service_type ?? []))
                         <li><label><input type="radio" class="radio" {{($record->service_type === Feedback::EXTERNAL && $record->category_id === $category->id) ? 'checked': ''}} disabled>{{$category->name}}</label></li>
                    @endif
                @endforeach
                <li><label><input type="radio" class="radio" name="extenal_services" disabled> Others (Specify): <span class="inline-block min-w-[120px] border-b-[1px] border-black">&nbsp;</span></label></li>
              </ul>
            </div>
            <div class="p-2">
              <div class="font-bold">Internal Services</div>
              <ul class="ml-4">
                @foreach ($record->organization->categories as $category)
                    @if (in_array(Feedback::INTERNAL->value,$category->service_type ?? []))
                         <li><label><input type="radio" class="radio" {{($record->service_type === Feedback::INTERNAL && $record->category_id === $category->id) ? 'checked': ''}} disabled>{{$category->name}}</label></li>
                    @endif
                @endforeach
                <li><label><input name="internal_services" type="radio" class="radio" disabled> Others (Specify): <span class="inline-block min-w-[120px] border-b-[1px] border-black">&nbsp;</span></label></li>
              </ul>
            </div>
          </div>
        </div>
        <br>
        <div>
            <div class="text-justify"><span class="font-bold">INSTRUCTIONS:</span> Check mark (✔) your answer to the Citizen's Charter (CC) questions. The Citizen's Charter is an official document that reflects the services of a government agency/office including its requirements, fees, and processing times among others.</div>
            <br>
            <div>
                <div>CC1 &nbsp; Which of the following best describes your awareness of a CC?</div>
                @php
                    $CC1 = $record->getAnswer('CC1');
                    $cc1_choices = [
                        1 => 'I know what a CC is and I saw the CC of this office.',
                        2 => 'I know what a CC is but I did NOT see the CC of this office.',
                        3 => 'I learned of the CC only when I saw the CC of this office.',
                        4 => 'I do not know what a CC is and I did not see the CC of this office. (Answer "N/A" on CC2 and CC3)',
                    ];
                @endphp
                <div class="flex flex-col ml-8">
                    @foreach ($cc1_choices as $cc1=>$text)
                        <label><input name="internal_services" type="radio" class="radio" disabled name="cc1" value="{{$cc1}}" {{$CC1 == $cc1 ? 'checked' : ''}} disabled> {{$text}}</label>
                    @endforeach
                </div>
            </div>
            <div>
                @php
                    $CC2 = $record->getAnswer('CC2');

                    $CC2_choices =[
                        1 => '1. Easy to see',
                        2 => '2. Somewhat easy to see',
                        3 => '3. Difficult to see',
                        4 => '4. Not visible at all',
                        5 => '5. N/A',
                    ];

                @endphp
                <div>CC2 &nbsp; If aware of CC (answered 1-3 in CC1), would you say that the CC of this office was…</div>
                <div class="grid grid-cols-2 ml-8">
                    @foreach ($CC2_choices as $cc2 => $text)
                        <label><input type="radio" class="radio" name="cc2" value="{{$cc2}}" {{$CC2 == $cc2 ? 'checked' : ''}} disabled> {{$text}}</label>
                    @endforeach
                </div>
            </div>
            <div>
                @php
                    $CC3 = $record->getAnswer('CC3');

                    $CC3_choices = [
                        1 => '1. Helped very much',
                        2 => '2. Somewhat helped',
                        3 => '3. Did not help',
                        4 => '4. N/A',
                    ];
                @endphp
              <div>CC3 &nbsp; If aware of CC (answered codes 1-3 in CC1), how much did the CC help you in your transaction?</div>
              <div class="grid grid-cols-2 ml-8">
                @foreach ($CC3_choices as $cc3 => $text)
                    <label><input type="radio" class="radio" name="cc3" value="{{$cc3}}" {{$CC3 == $cc3 ? 'checked' : ''}} disabled> {{$text}}</label>
                @endforeach
              </div>
            </div>
        </div>
        <br>
        <div>
            <div class="mb-2"><span class="font-bold">INSTRUCTIONS:</span> For SQD 0-8, please put a <span class="label">check mark (✔)</span> on the column that best corresponds to your answer.</div>

                <table class="table-fixed">
                  <colgroup>
                    <col style="width: 39%;">
                    <col style="width: 10%">
                    <col style="width: 10%">
                    <col style="width: 11%">
                    <col style="width: 10%">
                    <col style="width: 10%">
                    <col style="width: 10%">
                  </colgroup>
                  <tr class="text-center align-top">
                    <td class="border border-black"></td>
                    <td class="border border-black"><span class="emoji">😕</span><div>Strongly Disagree</div></td>
                    <td class="border border-black"><span class="emoji">🙁</span><div>Disagree</div></td>
                    <td class="border border-black"><span class="emoji">😐</span><div>Neither Agree nor Disagree</div></td>
                    <td class="border border-black"><span class="emoji">🙂</span><div>Agree</div></td>
                    <td class="border border-black"><span class="emoji">😄</span><div>Strongly Agree</div></td>
                    <td class="border border-black"><div>N/A</div></td>
                  </tr>
                  @php

                    $sqd = [
                      'SQD0. I am satisfied with the service that I availed.',
                      'SQD1. I spent a reasonable amount of time for my transaction.',
                      "SQD2. The office followed the transaction’s requirements and steps based on the information provided.",
                      'SQD3. The steps (including payment) I needed to do for my transaction were easy and simple.',
                      'SQD4. I easily found information about my transaction from this office or its website.',
                      "SQD5. I paid a reasonable amount of fees for my transaction. (if service is free, mark ‘N/A’ column)",
                      'SQD6. I feel the office was fair to everyone, or “walang palakasan”, during my transaction.',
                      'SQD7. I was treated courteously by the staff, and (if asked for help) the staff was helpful.',
                      'SQD8. I got what I needed from this office, or (if denied) denial of request was sufficiently explained to me.',
                    ];
                  @endphp
                  @foreach($sqd as $index => $text)
                    <tr class="text-justify leading-none h-8">
                      <td class="border border-black p-1">{{ $text }}</td>
                      @for($i=1; $i<=6; $i++)
                        <td class="border border-black text-center">
                            <x-filament::icon
                                icon="heroicon-s-check"
                                class="h-5 w-5 text-black dark:text-gray-400 place-self-center {{ $record->getAnswer('SQD'. $index) == $i ? '' : 'hidden' }}"
                            />
                        </td>
                      @endfor
                    </tr>
                  @endforeach
                </table>
            <br>`
            <div class="font-bold">Did we meet your expectations?</div>
            <div class="flex flex-col ml-8">
              <label ><input type="checkbox" class="checkbox" name="expectations[]" value="Exceeded" {{ $record->expectation == '1' ? 'checked' : '' }}> Exceeded Expectations</label>
              <label ><input type="checkbox" class="checkbox" name="expectations[]" value="Met" {{ $record->expectation == '2' ? 'checked' : '' }}> Met Expectations</label>
              <label ><input type="checkbox" class="checkbox" name="expectations[]" value="Fell Short" {{ $record->expectation == '3' ? 'checked' : '' }}> Fell Short</label>
            </div>
            <br>
            <div >What did you like the most about our service?</div>
            <span class="inline-block w-full border-b-[1px] border-black">{{$record->strength}}</span><br>
            <span class="inline-block w-full border-b-[1px] border-black"></span>

            <div>Comments/Suggestions on how we can further improve our services (optional)</div>
            <span class="inline-block w-full border-b-[1px] border-black">{{$record->improvement}}</span><br>
            <span class="inline-block w-full border-b-[1px] border-black"></span>
            <br>
            <div>
              <span>Email address (optional):</span> <span class="inline-block min-w-[120px] border-b-[1px] border-black">&nbsp;</span>
            </div>
            <div class="font-bold">THANK YOU!</div>
        </div>
    </div>
</section>
