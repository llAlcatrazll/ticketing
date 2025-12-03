@php
    $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
    $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
    $logoSrc = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('assets/logo/Province Logo.png')));
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Feedback Form</title>
    <style>
        {!! file_get_contents(public_path('build/' . $cssFile)) !!}
        .checkbox, .radio { margin-right: 6px; vertical-align: middle; }
        input.checkbox, input.radio {
            width: 10px; height: 10px; margin-top: -2px; cursor: pointer;
            appearance: none; -webkit-appearance: none; -moz-appearance: none;
            border: 1px solid #000; border-radius: 2px; background: #fff; position: relative;
            display: inline-block;
          }
          input.checkbox:checked::after, input.radio:checked::after {
              content: "\2713";
              position: absolute; top: -5px; left: 0px; font-size: 14px; line-height: 14px;
          }
          @font-face {
                font-family: 'Noto Color Emoji';
                src: url('/usr/share/fonts/truetype/noto/NotoColorEmoji.ttf') format('truetype');
            }
            .emoji {
                font-family: 'Noto Color Emoji', sans-serif;
                font-size: 14px;
                vertical-align: middle;
            }
    </style>
</head>
<body class="m-0 p-0 text-[10pt] leading-[11pt]">

@if($preview)
    @include('filament.panels.feedback.template', ['record'=>$record])
@else
    @foreach ($records as $record)
        @include('filament.panels.feedback.template', ['record'=>$record])
        @pageBreak
    @endforeach
@endif

</body>
</html>
