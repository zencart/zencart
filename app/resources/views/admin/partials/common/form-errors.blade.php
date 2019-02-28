<section class="validation-errors">
    @if (count($tplVars['errorMessages']) > 0)
        @foreach (collect($tplVars['errorMessages'])->all() as $error)
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ $error }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endforeach
    @endif
</section>
