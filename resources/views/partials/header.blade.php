<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ config('app.name') }}</title>
<link rel="stylesheet" href="{{ mix('css/app.css') }}">
<script src="{{ mix('js/app.js') }}"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } });
document.addEventListener("DOMContentLoaded", function() {
    if (document.querySelector('form.keepAlive') !== null) {
        setInterval(function() { axios.post('/keepTokenAlive'); }, 1000 * 60 * 15);
    }
    $('.search-select').select2({matcher:optgroupMatcher});
    $('[data-bs-toggle="tooltip"]').tooltip();
});
$winColor  = '#7BC75B';
$drawColor = '#003282';
$lossColor = '#FA5F69';
$chartColors = ['#1F87FF', '#003282', '#B5D7FF', '#FAD155', '#7BC75B', '#9D61D9', '#FF9A4A', '#53D1BF', '#005E85', '#B257A0', '#FFDBBF', '#13B3F2', '#5A8A46', '#FA5F69', '#73205F', '#B6E5FA', '#A38423', '#DAC4F2', '#1D7A67', '#FFC7CA'];
</script>
</head>
