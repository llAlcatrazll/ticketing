<?php

namespace App\Filament\Panels\Feedback\Pages;

use App\Enums\Feedback as EnumsFeedback;
use App\Models\Category;
use App\Models\Feedback as ModelsFeedback;
use App\Models\Organization;
use App\Models\Request;
use Exception;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\SimplePage;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\HtmlString;

class Feedback extends SimplePage implements HasForms
{
    use InteractsWithForms, InteractsWithFormActions;

    protected static string $model = ModelsFeedback::class;

    protected static string $layout = 'filament-panels::components.layout.base';

    protected static string $view = 'filament.panels.feedback.pages.feedback';

    public string|null|Organization $organization = null;

    public string|null|Request $request = null;

    public array $data = [];

    public function mount(string|null $organization = null): void
    {
        $this->organization = Organization::findOrFail($organization);
        $this->form->fill();
        $this->data['date'] = now()->toDateString();

        if(request()->has('request')){
            $this->request = Request::findOrFail(request()->get('request'));
        }
    }

    public static function canAccess(): bool
    {
        return true;
    }

    public static function registerRoutes(): void
    {
        Route::get('{organization}/feedback', static::class)
            ->name('feedback');
    }

    public function submit()
    {

        try{
            $this->form->validate();

            DB::beginTransaction();

            $cc = [
                'CC1' => $this->data['CC1'],
                'CC2' => $this->data['CC2'],
                'CC3' => $this->data['CC3'],
            ];

            $sqd = [
                'SQD0' => $this->data['SQD0'],
                'SQD1' => $this->data['SQD1'],
                'SQD2' => $this->data['SQD2'],
                'SQD3' => $this->data['SQD3'],
                'SQD4' => $this->data['SQD4'],
                'SQD5' => $this->data['SQD5'],
                'SQD6' => $this->data['SQD6'],
                'SQD7' => $this->data['SQD7'],
                'SQD8' => $this->data['SQD8'],
            ];
            $category_id = explode('-',$this->data['category_id']);
            $feedback_id = ModelsFeedback::create([
                'email' => $this->data['email'],
                'client_type' => $this->data['client_type'] ?? null,
                'age' => $this->data['age'] ?? null,
                'gender' => $this->data['gender'] ?? null,
                'residence' => $this->data['residence'],
                'service_type' => $this->data['service_type'] ?? null,
                'category_id' => $category_id[0],
                'service_type' => $category_id[1],
                'expectation' => $this->data['expectation'] ?? null,
                'strength' => $this->data['strength'] ?? null,
                'improvement' => $this->data['improvement'] ?? null,
                'organization_id' => $this->organization->id,
                'request_id' => $this->request->id ?? null,
                'user_id' => $this->request->user_id ?? null,
            ]);

            foreach($cc as $question => $answer){
                $feedback_id->responses()->create([
                    'question_type' => 'Citizen Charter',
                    'question' => $question,
                    'answer' => $answer,
                ]);
            }

            foreach($sqd as $question => $answer){
                $feedback_id->responses()->create([
                    'question_type' => 'Service Quality Dimension',
                    'question' => $question,
                    'answer' => $answer,
                ]);
            }

            DB::commit();
            $this->form->fill();

            return redirect()->route('filament.feedback.thank-you', ['organization' => $this->organization->id]);


            }catch(Exception $e){
                DB::rollBack();

                Notification::make()
                ->title('An '.$e->getMessage().' error occurred while submitting your feedback. Please try again.')
                ->danger()
                ->send();
                return;
            }

    }

    public function form (Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Wizard::make([
                    Step::make('Privacy Policy')
                        ->schema([
                            TextInput::make('email')
                                ->email()
                                ->label('Email Address')
                                ->placeholder('Email Address')
                                ->required()
                                ->default(fn () => request()->user()->email ?? null),
                            DatePicker::make('date')
                                ->label('Date')
                                ->required()
                                ->disabled(),
                            Checkbox::make('consent')
                                ->accepted()
                                ->required()
                                ->label(function (){
                                    $html =
                                        <<<'HTML'
                                        <div class="text-justify inline-block">

                                        In compliance with the  <b class="text-amber-600">Republic Act 10173 (RA 10173)</b> or also known as  <i>Data Privacy Act of 2012</i>,
                                        we are committed to protecting your personal information and ensuring its confidentiality and security.
                                        We kindly seek your explicit consent to collect, process, and store your personal data for legitimate and
                                        authorized purposes related to our services. Please rest assured that your information will be handled in
                                        accordance with the principles of transparency, accountability, and lawful processing, with all necessary security
                                        measures in place to protect it from unauthorized access or misuse. By providing your consent, you acknowledge
                                        and agree to the terms outlined in our privacy policy.

                                        </div>

                                        HTML;

                                    return new HtmlString($html);
                                })
                                ->validationMessages([
                                    'accepted' => 'You must accept the privacy policy to proceed.',
                                ])
                                ->extraAttributes(['class' => 'place-self-start mt-1']),
                        ]),
                    Step::make('Personal Information')
                        ->columns(2)
                        ->schema([
                            Select::make('client_type')
                                ->columnSpanFull()
                                ->label('Client Type')
                                ->required()
                                ->placeholder('Select your client type')
                                ->options(EnumsFeedback::clientTypesLabel())
                                ->default(fn () => request()->user() ? 'government' : null),

                            Select::make('gender')
                                ->label('Gender')
                                ->placeholder('Select your client type')
                                ->options([
                                    'male' => 'Male',
                                    'female' => 'Female',
                                    'other' => 'Other',
                                ]),
                            TextInput::make('age')
                                ->label('Age')
                                ->numeric()
                                ->placeholder('Enter your age')
                                ->rules(['min:1', 'max:120']),
                            MarkdownEditor::make('residence')
                                ->label('Place of Residence')
                                ->required()
                                ->placeholder('Enter your place of residence')
                                ->columnSpanFull(),
                        ]),
                    Step::make('Feedback')
                        ->schema([
                            Select::make('category_id')
                                ->label('Service Type')
                                ->required()
                                ->placeholder('Select the service availed')
                                ->reactive()
                                ->native(false)
                                ->allowHtml()
                                ->options(function (){
                                   return collect($this->organization->categories)->flatMap(function($category){
                                        return collect($category->service_type)->mapWithKeys(function($service_type) use ($category){
                                            $service_type_enums=EnumsFeedback::from($service_type)->getLabel();
                                            return [$category->id.'-'.$service_type => "<div class='flex flex-col'><span>{$category->name}</span><span class='italic opacity-50'>{$service_type_enums} Service</span></div>"];
                                        });
                                   });
                                }),
                            Section::make('Citizen Charter')
                                ->description('INSTRUCTIONS: Choose your answer to the Citizen’s Charter (CC) questions. The Citizen’s Charter is an official document that reflects the services of a government agency/office including its requirements, fees, and processing times among others.')
                                ->schema([
                                    Radio::make('CC1')
                                        ->label('CC1. Which of the following best describes your awareness of a CC?')
                                        ->options([
                                            '1'=> 'I know what a CC is and I saw the CC of this office.',
                                            '2'=> 'I know what a CC is but I did not see the CC of this office.',
                                            '3'=> 'I learned of the CC only when I saw the CC of this office.',
                                            '4'=> 'I do not know what a CC is and I did not see the CC of this office. (Answer ‘N/A’ on CC2 and CC3).'
                                        ])
                                        ->required()
                                        ->afterStateUpdated(function( $state, callable $set){
                                            if($state == '4'){
                                                $set('CC2', '5');
                                                $set('CC3', '4');
                                            }
                                        } )
                                        ->live(),
                                    Radio::make('CC2')
                                        ->label('CC2.If aware of CC (answered 1-3 in CC1), would you say that the CC of this office was...')
                                        ->options([
                                            '1' => 'Easy to see',
                                            '2' => 'Somewhat easy to see',
                                            '3' => 'Difficult to see',
                                            '4' => 'Not visible at all',
                                            '5' => 'N/A'
                                        ])
                                        ->required()
                                        ->disabled(fn ( $get) => $get('CC1') == '4'),
                                    Radio::make('CC3')
                                        ->label('CC3. If aware of CC (answered codes 1-3 in CC1), how much did the CC help you in your transaction?')
                                        ->options([
                                            '1' => 'Helped very much',
                                            '2' => 'Somewhat helped',
                                            '3' => 'Did not help',
                                            '4' => 'N/A'
                                        ])
                                        ->required()
                                        ->disabled(fn ( $get) => $get('CC1') == '4'),
                                ]),
                            Section::make('Service Quality Dimensions')
                                ->description('INSTRUCTIONS:  For SQD 0-8, please Choose on the column that best corresponds to your answer.')
                                ->schema([
                                    View::make('sqdoptions')
                                        ->view('filament.panels.feedback..components.sqdlegend'),
                                    Radio::make('SQD0')
                                        ->label('SQD0. I am satisfied with the service that I availed.')
                                        ->options([
                                            '1' => '1',
                                            '2' => '2',
                                            '3' => '3',
                                            '4' => '4',
                                            '5' => '5',
                                        ])
                                        ->inline()
                                        ->extraAttributes(['class'=> 'flex-col lg:flex-row lg:!gap-20'])
                                        ->required(),
                                    Radio::make('SQD1')
                                        ->label('SQD1. I spent a reasonable amount of time for my transaction.')
                                        ->options([
                                            '1' => '1',
                                            '2' => '2',
                                            '3' => '3',
                                            '4' => '4',
                                            '5' => '5',
                                        ])
                                        ->inline()
                                        ->extraAttributes(['class'=> 'flex-col lg:flex-row lg:!gap-20'])
                                        ->required(),
                                    Radio::make('SQD2')
                                        ->label('SQD2. The office followed the transaction’s requirements and steps based on the information provided.')
                                        ->options([
                                            '1' => '1',
                                            '2' => '2',
                                            '3' => '3',
                                            '4' => '4',
                                            '5' => '5',
                                        ])
                                        ->inline()
                                        ->extraAttributes(['class'=> 'flex-col lg:flex-row lg:!gap-20'])
                                        ->required(),
                                    Radio::make('SQD3')
                                        ->label('SQD3. The steps (including payment) I needed to do for my transaction were easy and simple.')
                                        ->options([
                                            '1' => '1',
                                            '2' => '2',
                                            '3' => '3',
                                            '4' => '4',
                                            '5' => '5',
                                        ])
                                        ->inline()
                                        ->extraAttributes(['class'=> 'flex-col lg:flex-row lg:!gap-20'])
                                        ->required(),
                                    Radio::make('SQD4')
                                        ->label('SQD4. I easily found information about my transaction from this office or its website.')
                                        ->options([
                                            '1' => '1',
                                            '2' => '2',
                                            '3' => '3',
                                            '4' => '4',
                                            '5' => '5',
                                        ])
                                        ->inline()
                                        ->extraAttributes(['class'=> 'flex-col lg:flex-row lg:!gap-20'])
                                        ->required(),
                                    Radio::make('SQD5')
                                        ->label('SQD5. I paid a reasonable amount of fees for my transaction. (if service is free, mark ‘N/A’ column)')
                                        ->options([
                                            '1' => '1',
                                            '2' => '2',
                                            '3' => '3',
                                            '4' => '4',
                                            '5' => '5',
                                        ])
                                        ->inline()
                                        ->extraAttributes(['class'=> 'flex-col lg:flex-row lg:!gap-20'])
                                        ->required(),
                                    Radio::make('SQD6')
                                        ->label('SQD6. I feel the office was fair to everyone, or “walang palakasan”, during my transaction.')
                                        ->options([
                                            '1' => '1',
                                            '2' => '2',
                                            '3' => '3',
                                            '4' => '4',
                                            '5' => '5',
                                        ])
                                        ->inline()
                                        ->extraAttributes(['class'=> 'flex-col lg:flex-row lg:!gap-20'])
                                        ->required(),
                                    Radio::make('SQD7')
                                        ->label('SQD7. I was treated courteously by the staff, and (if asked for help) the staff was helpful.')
                                        ->options([
                                            '1' => '1',
                                            '2' => '2',
                                            '3' => '3',
                                            '4' => '4',
                                            '5' => '5',
                                        ])
                                        ->inline()
                                        ->extraAttributes(['class'=> 'flex-col lg:flex-row lg:!gap-20'])
                                        ->required(),
                                    Radio::make('SQD8')
                                        ->label('SQD8. I got what I needed from this office, or (if denied) denial of request was sufficiently explained to me.')
                                        ->options([
                                            '1' => '1',
                                            '2' => '2',
                                            '3' => '3',
                                            '4' => '4',
                                            '5' => '5',
                                        ])
                                        ->inline()
                                        ->extraAttributes(['class'=> 'flex-col lg:flex-row lg:!gap-20'])
                                        ->required(),
                                ])

                        ]),
                    Step::make('Suggestions')
                        ->schema([
                            Radio::make('expectation')
                                ->label('Did we meet your expectations?')
                                ->options([
                                    '1' => 'Exceeded Expectations',
                                    '2' => 'Met Expectations',
                                    '3' => 'Fell Short',
                                ])
                                ->extraAttributes(['class'=> 'flex-col lg:flex-row lg:!gap-20']),
                            MarkdownEditor::make('strength')
                                ->label('What did you like the most about our service?')
                                ->placeholder('Your answer here...')
                                ->columnSpanFull(),
                            MarkdownEditor::make('improvement')
                                ->label('Comments/Suggestions on how we can further improve our services (optional)')
                                ->placeholder('Your answer here...')
                                ->columnSpanFull(),
                        ])
                    ])
                    ->startOnStep(3)
                    ->submitAction(new HtmlString(Blade::render(<<<BLADE
                        <x-filament::button type="submit" size="sm">
                            Submit Feedback
                        </x-filament::button>
                        BLADE

                    )))
            ]);
    }

}
