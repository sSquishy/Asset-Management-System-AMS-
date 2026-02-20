@extends('layouts/edit-form', [
    'createText' => trans('admin/categories/general.create') ,
    'updateText' => trans('admin/categories/general.update'),
    'helpPosition'  => 'right',
    'helpText' => trans('help.categories'),
    'topSubmit'  => 'true',
    'formAction' => (isset($item->id)) ? route('categories.update', ['category' => $item->id]) : route('categories.store'),
])

@section('inputFields')

<!-- Type -->
<div class="form-group {{ $errors->has('category_type') ? ' has-error' : '' }}">
    <label for="category_type" class="col-md-3 control-label">{{ trans('general.type') }}</label>
    <div class="col-md-7 required">
        <x-input.select
            id="category_type"
            name="category_type"
            :options="$category_types"
            :selected="old('category_type', $item->category_type)"
            :disabled="$item->category_type!='' || $item->itemCount() > 0"
            style="min-width:350px"
            aria-label="category_type"
        />
        {!! $errors->first('category_type', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
    </div>
    <div class="col-md-7 col-md-offset-3">
        <p class="help-block">{!! trans('admin/categories/message.update.cannot_change_category_type') !!} </p>
    </div>
</div>

@include ('partials.forms.edit.name', ['translated_name' => trans('admin/categories/general.name')])

<!-- Parent -->
<div id="parent_div" class="form-group {{ $errors->has('parent_id') ? ' has-error' : '' }}">
    <label for="parent_id" class="col-md-3 control-label">{{ trans('Parent Asset') }}</label>
    <div class="col-md-7">
        <x-input.select
            id="parent_id"
            name="parent_id"
            :options="$parentOptions"
            :selected="old('parent_id', $item->parent_id ?? null)"
            style="min-width:350px; width:75%"
            aria-label="parent_id"
        />
        {!! $errors->first('parent_id', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
    </div>
</div>


<livewire:category-edit-form
    :alert-on-response="(bool) old('alert_on_response', $item->alert_on_response)"
    :default-eula-text="$snipeSettings->default_eula_text"
    :eula-text="old('eula_text', $item->eula_text)"
    :require-acceptance="(bool) old('require_acceptance', $item->require_acceptance)"
    :send-check-in-email="(bool) old('checkin_email', $item->checkin_email)"
    :use-default-eula="(bool) old('use_default_eula', $item->use_default_eula)"
/>

@include ('partials.forms.edit.image-upload', ['image_path' => app('categories_upload_path')])

<div class="form-group{!! $errors->has('notes') ? ' has-error' : '' !!}">
    <label for="notes" class="col-md-3 control-label">{{ trans('general.notes') }}</label>
    <div class="col-md-8">
        <x-input.textarea
                name="notes"
                id="notes"
                :value="old('notes', $item->notes)"
                placeholder="{{ trans('general.placeholders.notes') }}"
                aria-label="notes"
                rows="5"
        />
        {!! $errors->first('notes', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
    </div>
</div>


@stop

@section('content')
@parent


@if ($snipeSettings->default_eula_text!='')
<!-- Modal -->
<div class="modal fade" id="eulaModal" tabindex="-1" role="dialog" aria-labelledby="eulaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h2 class="modal-title" id="eulaModalLabel">{{ trans('admin/settings/general.default_eula_text') }}</h2>
            </div>
            <div class="modal-body">
                {{ \App\Models\Setting::getDefaultEula() }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('button.cancel') }}</button>
            </div>
        </div>
    </div>
</div>
@endif



@stop

@section('moar_scripts')
<script nonce="{{ csrf_token() }}">
    document.addEventListener('DOMContentLoaded', function () {
        // Try to find the select element (by id first, then by name)
        var typeSelect = document.getElementById('category_type') || document.querySelector('select[name="category_type"]');
        var parentDiv = document.getElementById('parent_div');
        if (!parentDiv) return;

        function selectContainsAsset(el) {
            if (!el) return false;
            var val = (el.value || '').toString().toLowerCase();
            if (val === 'asset') return true;

            // Check selected option text
            try {
                var txt = (el.options && el.options[el.selectedIndex]) ? el.options[el.selectedIndex].text : '';
                if (txt && txt.toString().toLowerCase() === 'asset') return true;
            } catch (e) {}

            // If Select2 replaced the element, check rendered container
            try {
                var sel2Container = document.querySelector('#' + (el.id || 'category_type') + ' + .select2-container .select2-selection__rendered');
                if (sel2Container && sel2Container.textContent && sel2Container.textContent.toLowerCase().indexOf('asset') !== -1) return true;
            } catch (e) {}

            return false;
        }

        function toggleParent() {
            var show = selectContainsAsset(typeSelect);
            if (show) {
                parentDiv.style.display = '';
            } else {
                parentDiv.style.display = 'none';
                var sel = parentDiv.querySelector('select[name="parent_id"]');
                if (sel) {
                    sel.value = '';
                    // trigger change for enhanced widgets
                    if (window.jQuery) try { jQuery(sel).trigger('change'); } catch (e) {}
                }
            }
        }

        // Initial run
        toggleParent();

        // Attach native change
        if (typeSelect) {
            typeSelect.addEventListener('change', toggleParent);
            // support Select2 events
            if (window.jQuery) {
                try {
                    jQuery(typeSelect).on('select2:select select2:unselect change', toggleParent);
                } catch (e) {}
            }
        }

        // Re-evaluate after Livewire updates
        document.addEventListener('livewire:update', function () {
            // re-resolve the element in case it was re-rendered
            typeSelect = document.getElementById('category_type') || document.querySelector('select[name="category_type"]');
            toggleParent();
        });
    });
</script>
@stop
