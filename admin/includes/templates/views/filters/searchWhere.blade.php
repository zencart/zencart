<label for="{{ $tpl['id'] }}">{{ $tpl['label'] }}</label>
<select {{ $tpl['class'] }} id="{{ $tpl['id'] }}" name="{{ $tpl['name'] }}" {!! $tpl['autoJs'] !!}>
@foreach( $tpl['options'] as $optVal => $optText )
    <option value="{{ $optVal }}" @if((string)$optVal == (string)$tpl['default']) selected @endif>{{ $optText }}</option>
@endforeach
</select>
