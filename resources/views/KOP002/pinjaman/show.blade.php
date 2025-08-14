@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])
{{-- section content --}}
@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => ''])
    <div class="card shadow-lg mx-4">
        <div class="card-body p-3">
            <div class="row gx-4">
                <div class="col-lg">
                    <div class="nav-wrapper">
                        {{-- button back --}}
                        <button class="btn btn-secondary mb-0" onclick="history.back()"><i class="fas fa-circle-left me-1">
                            </i><span class="font-weight-bold">Kembali</button>
                        {{-- check authorize edi --}}
                        @if ($authorize->edit == '1')
                            {{-- check status active --}}
                            @if ($list->isactive == 1)
                                {{-- button save --}}
                                <button class="btn btn-primary mb-0" style="display: none;" id="{{ $dmenu }}-save"
                                    onclick="event.preventDefault(); document.getElementById('{{ $dmenu }}-form').submit();"><i
                                        class="fas fa-floppy-disk me-1"> </i><span class="font-weight-bold">Simpan</button>
                                {{-- button edit --}}
                                <button class="btn btn-warning mb-0" id="{{ $dmenu }}-edit"><i
                                        class="fas fa-edit me-1"> </i><span class="font-weight-bold">Edit</button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <form role="form" method="POST" action="{{ URL::to($url_menu . '/' . $idencrypt) }}"
                        enctype="multipart/form-data" id="{{ $dmenu }}-form">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <p class="text-uppercase text-sm">View {{ $title_menu }}</p>
                            <hr class="horizontal dark mt-0">
                            <div class="row">
                                {{-- retrieve table header --}}
                                @foreach ($table_header as $header)
                                    @php
                                        $primary = false;
                                        $generateid = false;
                                        foreach ($table_primary as $p) {
                                            $primary == false
                                                ? ($p->field == $header->field
                                                    ? ($primary = true)
                                                    : ($primary = false))
                                                : '';
                                            $generateid == false
                                                ? ($p->generateid == $header->field
                                                    ? ($generateid = true)
                                                    : ($generateid = false))
                                                : '';
                                        }
                                    @endphp
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            @if ($header->type != 'hidden')
                                                {{-- display label alias on type field not hidden --}}
                                                <label for="example-text-input"
                                                    class="form-control-label">{{ $header->alias }}</label>
                                            @endif
                                            {{-- field type char and string --}}
                                            @if ($header->type == 'char' || $header->type == 'string')
                                                <input
                                                    class="form-control {{ $header->primary == '1' ? ' bg-dark text-light' : '' }} {{ $header->class }}"
                                                    type="text" disabled {{ $primary ? ' key=true' : '' }}
                                                    value="{{ $list ? $list->{$header->field} : old($header->field) }}"
                                                    name="{{ $header->field }}" maxlength="{{ $header->length }}">
                                                @if ($header->note != '')
                                                    <p class='text-secondary text-xs pt-1 px-1'>
                                                        {{ '*) ' . $header->note }}
                                                    </p>
                                                @endif
                                                {{-- field type text --}}
                                            @elseif ($header->type == 'text')
                                                <textarea class="form-control {{ $header->primary == '1' ? ' bg-dark text-light' : '' }} {{ $header->class }}"
                                                    disabled name="{{ $header->field }}" maxlength="{{ $header->length }}">{{ $list ? $list->{$header->field} : old($header->field) }}</textarea>
                                                @if ($header->note != '')
                                                    <p class='text-secondary text-xs pt-1 px-1'>
                                                        {{ '*) ' . $header->note }}
                                                    </p>
                                                @endif
                                                {{-- field type email --}}
                                            @elseif ($header->type == 'email')
                                                <input
                                                    class="form-control {{ $header->primary == '1' ? ' bg-dark text-light' : '' }} {{ $header->class }}"
                                                    type="email" disabled {{ $primary ? ' key=true' : '' }}
                                                    value="{{ $list ? $list->{$header->field} : old($header->field) }}"
                                                    name="{{ $header->field }}" maxlength="{{ $header->length }}">
                                                @if ($header->note != '')
                                                    <p class='text-secondary text-xs pt-1 px-1'>
                                                        {{ '*) ' . $header->note }}
                                                    </p>
                                                @endif
                                                {{-- field type number --}}
                                            @elseif ($header->type == 'number')
                                                <input
                                                    class="form-control {{ $header->primary == '1' ? ' bg-dark text-light' : '' }} {{ $header->class }}"
                                                    type="number" disabled {{ $primary ? ' key=true' : '' }}
                                                    value="{{ $list ? $list->{$header->field} : old($header->field) }}"
                                                    name="{{ $header->field }}" max="{{ $header->length }}">
                                                @if ($header->note != '')
                                                    <p class='text-secondary text-xs pt-1 px-1'>
                                                        {{ '*) ' . $header->note }}
                                                    </p>
                                                @endif
                                                {{-- field type currency --}}
                                            @elseif ($header->type == 'currency')
                                                <input
                                                    class="form-control {{ $header->primary == '1' ? ' bg-dark text-light' : '' }} {{ $header->class }}"
                                                    type="number" disabled {{ $primary ? ' key=true' : '' }}
                                                    value="{{ $list ? $list->{$header->field} : old($header->field) }}"
                                                    name="{{ $header->field }}" max="{{ $header->length }}">
                                                @if ($header->note != '')
                                                    <p class='text-secondary text-xs pt-1 px-1'>
                                                        {{ '*) ' . $header->note }}
                                                    </p>
                                                @endif
                                                {{-- field type search --}}
                                            @elseif ($header->type == 'search')
                                                <div class="flex flex-col mb-2 input-group">
                                                    <input name="{{ $header->field }}"
                                                        class="form-control {{ $header->primary == '1' ? ' bg-dark text-light' : '' }}  {{ $header->class }}"
                                                        type="text" disabled {{ $primary ? ' key=true' : '' }}
                                                        value="{{ $list ? $list->{$header->field} : old($header->field) }}">
                                                    <span class="input-group-text bg-primary text-light icon-modal-search"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#searchModal{{ $header->field }}"
                                                        style="border-color:#d2d6da;border-left:3px solid #d2d6da;cursor: pointer;display:none;"><i
                                                            class="fas fa-search"></i></span>
                                                </div>
                                                <!-- Modal -->
                                                <div class="modal fade" id="searchModal{{ $header->field }}" tabindex="-1"
                                                    role="dialog" aria-labelledby="searchModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="searchModalLabel">
                                                                    List Data
                                                                </h5>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            @if ($header->query != '')
                                                                @php
                                                                    $table_result = DB::select($header->query);
                                                                @endphp
                                                            @endif
                                                            <div class="modal-body">
                                                                <table class="table display" id="list_{{ $dmenu }}">
                                                                    @if ($table_result)
                                                                        <thead class="thead-light"
                                                                            style="background-color: #00b7bd4f;">
                                                                            <tr>
                                                                                <th width="20px">Action</th>
                                                                                @foreach ($table_result as $result)
                                                                                    @php
                                                                                        $sAsArray = array_keys(
                                                                                            (array) $result,
                                                                                        );
                                                                                    @endphp
                                                                                @endforeach
                                                                                @foreach ($sAsArray as $modal_h)
                                                                                    <th>{{ $modal_h }}</th>
                                                                                @endforeach
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach ($table_result as $modal_d)
                                                                                <tr>
                                                                                    @foreach ($table_result as $result)
                                                                                        @php
                                                                                            $field = array_keys(
                                                                                                (array) $result,
                                                                                            );
                                                                                        @endphp
                                                                                    @endforeach
                                                                                    <td width="20px"><span
                                                                                            class="btn badge bg-primary badge-lg"
                                                                                            onclick="select_modal('{{ $modal_d->{$field[0]} }}')"><i
                                                                                                class="bi bi-check-circle me-1"></i>
                                                                                            Select</span>
                                                                                    </td>
                                                                                    @foreach ($field as $header_field)
                                                                                        @php
                                                                                            $string = $header_field;
                                                                                        @endphp
                                                                                        <td
                                                                                            class="text-sm font-weight-normal">
                                                                                            {{ $modal_d->$string }}
                                                                                        </td>
                                                                                    @endforeach
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    @endif
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @if ($header->note != '')
                                                    <p class='text-secondary text-xs pt-1 px-1'>
                                                        {{ '*) ' . $header->note }}
                                                    </p>
                                                @endif
                                                <script>
                                                    function select_modal(id, name) {
                                                        $('input[name="{{ $header->field }}"]').val(id);
                                                        $('#searchModal{{ $header->field }}').modal('hide');
                                                    }
                                                </script>
                                                {{-- field type image --}}
                                            @elseif ($header->type == 'image')
                                                <div class="col-sm-auto">
                                                    <div class="position-relative">
                                                        <div>
                                                            <label for="file-input" style="left: -5px !important;"
                                                                id="{{ $header->field }}edit"
                                                                class="btn btn-xxl btn-icon-only bg-gradient-primary position-absolute bottom-0 mb-n2 disabled">
                                                                <i class="fa fa-pen top-0" data-bs-toggle="tooltip"
                                                                    disabled data-bs-placement="top" title=""
                                                                    aria-hidden="true" data-bs-original-title="Edit Image"
                                                                    aria-label="Edit Image"></i>
                                                                <span class="sr-only">Edit Image</span>
                                                            </label>
                                                            <span
                                                                class="h-12 w-12 rounded-full overflow-hidden bg-gray-100">
                                                                <img src="{{ asset('/storage' . '/' . $list->{$header->field}) }}"
                                                                    id="{{ $header->field }}preview" alt="image"
                                                                    data-bs-toggle="modal" data-bs-target="#imageModal"
                                                                    class="w-30 border-radius-lg shadow-sm">
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <input class="form-control {{ $header->class }}" type="file"
                                                    value="{{ $list ? $list->{$header->field} : old($header->field) }}"
                                                    id="{{ $header->field }}" name="{{ $header->field }}"
                                                    style="display: none;">
                                                <p class='text-primary text-xs pt-3 mb-0'>Maksimal Size :
                                                    <b>{{ $header->length }} KB</b>
                                                </p>
                                                @if ($header->note != '')
                                                    <p class='text-primary text-xs pt-1'>
                                                        {{ $header->note }}
                                                    </p>
                                                @else
                                                    <p class='text-primary text-xs pt-1'>Format Image :
                                                        <b>PNG,JPG,JPEG</b>
                                                    </p>
                                                @endif
                                                <!-- Modal -->
                                                <div class="modal fade" id="imageModal" tabindex="-1" role="dialog"
                                                    aria-labelledby="imageModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="imageModalLabel">Preview Image
                                                                </h5>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <img src="{{ asset('/storage' . '/' . $list->{$header->field}) }}"
                                                                    alt="image"
                                                                    class="w-100 border-radius-lg shadow-sm">
                                                            </div>
                                                            <div class="modal-footer">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <script>
                                                    {{ $header->field }}.onchange = evt => {
                                                        const [file] = {{ $header->field }}.files
                                                        if (file) {
                                                            {{ $header->field . 'preview' }}.src = URL.createObjectURL(file)
                                                        }
                                                    }
                                                    $('#{{ $header->field }}edit').click(function() {
                                                        $('input[name="{{ $header->field }}"]').click();

                                                    });
                                                </script>
                                                {{-- field type password --}}
                                            @elseif ($header->type == 'password')
                                                <div class="flex flex-col mb-2 input-group pass">
                                                    <input class="form-control {{ $header->class }}" disabled
                                                        {{ $primary ? ' key=true type=hidden' : ($header->filter == '1' ? ' type=password' : ' type=hidden') }}
                                                        value="{{ $list ? '' : $header->default }}"
                                                        name="{{ $header->field }}" max="{{ $header->length }}">
                                                    <span class="input-group-text" id="button-addon"
                                                        style="border-color:#d2d6da;"><i class="fas fa-eye showpass"
                                                            style="cursor: pointer;"></i></span>
                                                </div>
                                                <p class='text-primary text-xs pt-1'>Default Password :
                                                    <b>{{ $header->default }}</b>
                                                </p>
                                                {{-- field type file --}}
                                            @elseif ($header->type == 'file')
                                                <input class="form-control {{ $header->class }}" type="file" disabled
                                                    id="{{ $header->field }}edit"
                                                    value="{{ $list ? $list->{$header->field} : old($header->field) }}"
                                                    name="{{ $header->field }}">
                                                <p class='text-info text-xs pt-1 px-1 mb-1'>
                                                    {{ $list ? $list->{$header->field} : old($header->field) }}
                                                </p>
                                                <p class='text-primary text-xs pt-3 mb-0'>Maksimal Size :
                                                    <b>{{ $header->length }} KB</b>
                                                </p>
                                                @if ($header->note != '')
                                                    <p class='text-primary text-xs pt-1'>
                                                        {{ $header->note }}
                                                    </p>
                                                @else
                                                    <p class='text-primary text-xs pt-1'>Format File :
                                                        <b>pdf,xls,xlsx</b>
                                                    </p>
                                                @endif
                                                {{-- field type date --}}
                                            @elseif ($header->type == 'date')
                                                <input
                                                    class="form-control {{ $header->primary == '1' ? ' bg-dark text-light' : '' }} {{ $header->class }}"
                                                    type="date" disabled {{ $primary ? ' key=true' : '' }}
                                                    value="{{ $list ? $list->{$header->field} : old($header->field) }}"
                                                    name="{{ $header->field }}">
                                                @if ($header->note != '')
                                                    <p class='text-secondary text-xs pt-1 px-1'>
                                                        {{ '*) ' . $header->note }}
                                                    </p>
                                                @endif
                                                {{-- field type hidden --}}
                                            @elseif ($header->type == 'hidden')
                                                <input class="form-control {{ $header->class }}" type="hidden"
                                                    value="{{ $header->default }}" name="{{ $header->field }}"
                                                    max="{{ $header->length }}">
                                                {{-- field type enum --}}
                                            @elseif ($header->type == 'enum')
                                                <select
                                                    class="form-select {{ $header->primary == '1' ? ' bg-dark text-light' : '' }} {{ $header->class }}"
                                                    {{ Str::contains($header->class, 'select-multiple') ? 'multiple' : '' }}
                                                    name="{{ $header->field }}{{ Str::contains($header->class, 'select-multiple') ? '[]' : '' }}"
                                                    disabled {{ $primary || $generateid ? ' key=true' : '' }}>
                                                    <option value=""></option>
                                                    @if ($header->query != '')
                                                        @php
                                                            $data_query = DB::select($header->query);
                                                        @endphp
                                                        @if (Str::contains($header->class, 'select-multiple'))
                                                            <?php $multiple = array_map('trim', explode(',', $list->{$header->field})); ?>
                                                            @foreach ($data_query as $q)
                                                                <?php $sAsArray = array_values((array) $q); ?>
                                                                <option value="{{ $sAsArray[0] }}"
                                                                    {{ in_array($sAsArray[0], $multiple) ? 'selected' : '' }}>
                                                                    {{ $sAsArray[1] }}
                                                                </option>
                                                            @endforeach
                                                        @else
                                                            @foreach ($data_query as $q)
                                                                <?php $sAsArray = array_values((array) $q); ?>
                                                                <option value="{{ $sAsArray[0] }}"
                                                                    {{ $sAsArray[0] == $list->{$header->field} ? 'selected' : '' }}>
                                                                    {{ $sAsArray[1] }}
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                    @endif
                                                </select>
                                                @if ($header->note != '')
                                                    <p class='text-secondary text-xs pt-1 px-1'>
                                                        {{ '*) ' . $header->note }}
                                                    </p>
                                                @endif
                                                <script>
                                                    $(this).val('{{ $list->{$header->field} }}')
                                                </script>
                                            @endif
                                            @error($header->field)
                                                <p class='text-danger text-xs pt-1'> {{ $message }} </p>
                                            @enderror
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="row px-2 py-2">
                                <div class="col-lg">
                                    <div class="nav-wrapper"><code>Note : <i aria-hidden="true" style=""
                                                class="fas fa-circle text-dark"></i> Data primary key</code></div>
                                </div>
                            </div>
                            <hr class="horizontal dark">
                        </div>
                        <div class="card-footer align-items-center pt-0 pb-2">

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- check flag js on dmenu --}}
    @if ($jsmenu == '1')
        @if (view()->exists("js.{$dmenu}"))
            @push('addjs')
                {{-- file js in folder (resources/views/js) --}}
                @include('js.' . $dmenu);
            @endpush
        @else
            @push('addjs')
                <script>
                    Swal.fire({
                        title: 'JS Not Found!!',
                        text: 'Please Create File JS',
                        icon: 'error',
                        confirmButtonColor: '#028284'
                    });
                </script>
            @endpush
        @endif
    @endif
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            //set disable all input form
            $('#{{ $dmenu }}-form').find('label').addClass('disabled');
            $('#{{ $dmenu }}-form').find('input').attr('disabled', 'disabled');
            $('#{{ $dmenu }}-form').find('select').attr('disabled', 'disabled');
            $('#{{ $dmenu }}-form').find('textarea').attr('disabled', 'disabled');
            $('#{{ $dmenu }}-form').find('input[key="true"]').parent('.form-group').css('display',
                '');
            $('#{{ $dmenu }}-form').find('select[key="true"]').parent('.form-group').css('display',
                '');
            $('.icon-modal-search').css('display', 'none');
            // function enable input form
            function enable_text() {
                $('#{{ $dmenu }}-form').find('label').removeClass('disabled');
                $('#{{ $dmenu }}-form').find('input').removeAttr('disabled');
                $('#{{ $dmenu }}-form').find('select').removeAttr('disabled');
                $('#{{ $dmenu }}-form').find('textarea').removeAttr('disabled');
                $('#{{ $dmenu }}-form').find('input[key="true"]').parent('.form-group').css('display',
                    'none');
                $('#{{ $dmenu }}-form').find('select[key="true"]').parent('.form-group').css('display',
                    'none');
                $('.icon-modal-search').css('display', '');
            }
            //event button edit
            $('#{{ $dmenu }}-edit').click(function() {
                enable_text();
                $(this).css('display', 'none');
                $('#{{ $dmenu }}-save').css('display', '');
            });
        });
    </script>
@endpush
