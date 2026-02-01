@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="{{ config('app.url') }}/assets/image/logo.png" class="logo" alt="{{ \App\Models\AppSetting::getSetting('name') ?? config('app.name', 'PSC-MusicWeb Project') }} Logo">
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
