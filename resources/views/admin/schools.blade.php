@extends('admin.layout')

@section('content')
    <div class="flex justify-end p-3">
        <button type="button" data-modal-target="newEntryModal" data-modal-toggle="newEntryModal" class="mr-1 bg-green-500 hover:bg-green-600 rounded-md py-1 px-2 text-white">
            <i class="material-symbols-outlined text-sm">add</i>
        </button>
    </div>

    {{-- @if ($errors->any())
        @if (Session::get('newForm'))
            <input type="hidden" name="errorFlag" id="errorFlag" value="1">
        @else
            <input type="hidden" name="errorFlag" id="errorFlag" value="2">
        @endif
    @endif --}}

    <div class="p-3 overflow-x-scroll">
        <table class="myTable bg-white">
            <thead>
                <th>Sr No</th>
                <th>Name</th>
                <th>Action</th>
            </thead>

            <tbody>
                @php
                    $i=1;
                @endphp
                @foreach ($regions as $region)
                    <tr class="odd:bg-gray-200 even:bg-white">
                        <td>{{ $i }}</td>
                        <td class="capitalize">{{ $region['schoolName'] }}</td>
                        
                        <td>
                            <button data-modal-target="editEntryModal" data-modal-toggle="editEntryModal" type="button" class="rounded-md bg-blue-500 text-white py-1 px-2 hover:bg-blue-600" onclick="editEntry({{ $region['schoolId'] }})">
                                <i class="material-symbols-outlined text-sm">border_color</i>
                            </button>

                            <button data-modal-target="delEntryModal" data-modal-toggle="delEntryModal" type="button" class="mx-1 rounded-md bg-red-500 text-white py-1 px-2 hover:bg-red-600" onclick="handleDel({{ $region['schoolId'] }})">
                                <i class="material-symbols-outlined text-sm">delete</i>
                            </button>
                        </td>
                    </tr> 

                    @php
                        $i++;
                    @endphp
                @endforeach
            </tbody>
        </table>
    </div>

    @include('modals.newSchool')
    @include('modals.editSchool')
    @include('modals.rejectModal')

    <script>
        // var errorFlag=$("#errorFlag").val();

        // if(errorFlag==1){
        //     $("body").addClass('overflow-hidden');
        //     $("#newAdminModal").removeClass('hidden');
        //     $("#newAdminModal").addClass('flex');
        //     $("#newAdminModal").attr('aria-modal',true);
        //     $("#newAdminModal").attr('role',"dialog");
        // }

        // if(errorFlag==2){
        //     $("body").addClass('overflow-hidden');
        //     $("#editAdminModal").removeClass('hidden');
        //     $("#editAdminModal").addClass('flex');
        //     $("#editAdminModal").attr('aria-modal',true);
        //     $("#editAdminModal").attr('role',"dialog");
        // }

        // function changeActivity(id){
        //     $("#truckId2").val(id);
        // }

        function editEntry(id){
            $("#editEntryModal").removeClass('hidden'); 

            $.ajax({
                type:"GET",
                url:`{{ url('/schoolInfo') }}/${id}`,
                success: function($response){
                    if($response.status==200){
                        $(`#updatedSchoolName`).val($response.data.fieldName);
                        $("#entryId").val(id);   
                    }
                }
            });
        }

        function handleDel(id){
            $("#delEntryModal").removeClass('hidden'); 
            $("#delEntryId").val(id);  
        }
    </script>
@endsection