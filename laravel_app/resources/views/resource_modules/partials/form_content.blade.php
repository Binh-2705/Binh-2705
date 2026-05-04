@php $fileFields = $moduleConfig['file_fields'] ?? []; @endphp
<section class="panel">
    <form method="post" action="{{ $mode === 'create' ? route(($routeKey ?? $moduleKey) . '.store') : route(($routeKey ?? $moduleKey) . '.update', ['record' => $recordId]) }}" {{ count($fileFields) ? 'enctype="multipart/form-data"' : '' }}>
        @csrf
        @if ($mode === 'edit')
            @method('PUT')
        @endif

        @php $primaryKeys = is_array($resourceConfig['primary_key']) ? $resourceConfig['primary_key'] : [$resourceConfig['primary_key']] @endphp
        <div class="field-grid">
            @foreach ($resourceConfig['columns'] as $column)
                @php
                    $field = $column['field'];
                    $isPrimaryKey = in_array($field, $primaryKeys, true);
                    $isAutoIncrement = str_contains($column['extra'], 'auto_increment');
                    $shouldDisable = ($mode === 'edit' && $isPrimaryKey) || $isAutoIncrement;
                    $value = old($field, data_get($record, $field, $column['default']));
                    $isTextarea = str_contains($column['type'], 'text');
                    $inputType = 'text';
                    if (str_contains($column['type'], 'date') && !str_contains($column['type'], 'datetime')) {
                        $inputType = 'date';
                    } elseif (str_contains($column['type'], 'time')) {
                        $inputType = 'time';
                    } elseif (str_contains($column['type'], 'int') || str_contains($column['type'], 'decimal') || str_contains($column['type'], 'float') || str_contains($column['type'], 'double')) {
                        $inputType = 'number';
                    }
                @endphp
<div class="{{ $isTextarea ? 'full-span' : '' }}">
                    <label for="{{ $field }}">{{ $field }}</label>
                    @if (in_array($field, $fileFields, true))
                        @if ($value)
                            <div class="top-gap-sm" style="margin-bottom:6px">
                                <img src="{{ route('legacy.upload', ['path' => 'photos/' . $value]) }}" alt="Ảnh hiện tại" style="max-height:80px;border-radius:4px;border:1px solid #e5e7eb;">
                            </div>
                        @endif
                        <input id="{{ $field }}" name="{{ $field }}" type="file" accept="image/*" {{ $shouldDisable ? 'disabled' : '' }}>
                    @elseif ($isTextarea)
                        <textarea id="{{ $field }}" name="{{ $field }}" {{ $shouldDisable ? 'disabled' : '' }}>{{ $value }}</textarea>
                    @else
                        <input id="{{ $field }}" name="{{ $field }}" type="{{ $inputType }}" value="{{ $value }}" {{ $shouldDisable ? 'disabled' : '' }} {{ !$column['nullable'] && !$shouldDisable ? 'required' : '' }}>
                    @endif
                    <div class="muted top-gap-sm">{{ $column['type'] }}{{ $column['extra'] ? ' | ' . $column['extra'] : '' }}</div>
                </div>
            @endforeach
        </div>

        <div class="form-actions-bar">
            <button class="btn" type="submit">{{ $mode === 'create' ? 'Tạo bản ghi' : 'Cập nhật bản ghi' }}</button>
            <a class="btn btn-secondary" href="{{ route(($routeKey ?? $moduleKey) . '.index') }}">Về danh sách</a>
        </div>
    </form>
</section>
