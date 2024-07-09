@extends('admin.layout')

@section('content')
    <div class="flex justify-end p-3">
        <button type="button" data-modal-target="newAdminModal" data-modal-toggle="newAdminModal" class="mr-1 bg-green-500 hover:bg-green-600 rounded-md py-1 px-2 text-white">
            <i class="material-symbols-outlined text-sm">add</i>
        </button>
    </div>

    @if ($errors->any())
        @if (Session::get('newAdminForm'))
            <input type="hidden" name="errorFlag" id="errorFlag" value="1">
        @else
            <input type="hidden" name="errorFlag" id="errorFlag" value="2">
        @endif
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="px-3 text-red-500 italic text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="p-3 overflow-x-scroll">
        <table class="myTable bg-white">
            <thead>
                <th>Sr No</th>
                <th>Name</th>
                <th>Contact Details</th>
                <th>Other Details</th>
                @if (count($userData)>1)
                    <th>Activity</th>
                @endif
                <th>Action</th>
            </thead>

            <tbody>
                @php
                    $i=1;
                @endphp
                @foreach ($userData as $user)
                    <tr class="odd:bg-gray-200 even:bg-white">
                        <td>{{ $i }}</td>
                        <td>
                            <p class="capitalize">{{ $user['userName'] }}</p>
                            @if (Session::get('pageTitle')=='Teachers')
                                <p><b>R.No: {{ $user['registrationNumber'] }}</b></p>
                            @endif
                        </td>

                        <td>
                            <p><b>Email:</b> {{ $user['email'] }}</p>
                            <p><b>Contact:</b> {{ $user['mobile'] }}</p>
                        </td>

                        <td>
                            @if (Session::get('pageTitle')=='Teachers')
                                <p><b>School:</b> {{ $user['schoolName'] }}</p>
                            @endif

                            <p><b>Region:</b> {{ $user['regionName'] }}</p>
                            <p><b>District:</b> {{ $user['districtName'] }}</p>
                            <p><b>Ward:</b> {{ $user['wardName'] }}</p>
                        </td>
                     
                        @if (count($userData)>1)
                            <td>
                                <label class="relative inline-flex items-center cursor-pointer" onclick="changeActivity({{ $user['userId'] }})">
                                    <input data-modal-target="activityModal" data-modal-toggle="activityModal" type="checkbox" value="" class="sr-only peer" @checked($user['isActive']==1)>
                                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-600"></div>
                                </label>
                            </td>
                        @endif
                        
                        <td>
                            <button data-modal-target="editAdminModal" data-modal-toggle="editAdminModal" type="button" class="rounded-md bg-blue-500 text-white py-1 px-2 hover:bg-blue-600" onclick="editAdmin({{ $user['userId'] }})">
                                <i class="material-symbols-outlined text-sm">border_color</i>
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

    @include('modals.newAdmin')
    @include('modals.editAdmin')
    @include('modals.activityModal')

    <script>
        var errorFlag=$("#errorFlag").val();

        if(errorFlag==1){
            $("body").addClass('overflow-hidden');
            $("#newAdminModal").removeClass('hidden');
            $("#newAdminModal").addClass('flex');
            $("#newAdminModal").attr('aria-modal',true);
            $("#newAdminModal").attr('role',"dialog");
        }

        if(errorFlag==2){
            $("body").addClass('overflow-hidden');
            $("#editAdminModal").removeClass('hidden');
            $("#editAdminModal").addClass('flex');
            $("#editAdminModal").attr('aria-modal',true);
            $("#editAdminModal").attr('role',"dialog");
        }

        function changeActivity(id){
            $("#activityModal").removeClass('hidden'); 
            $("#truckId2").val(id);
        }

        function editAdmin(id){
            $("#editAdminModal").removeClass('hidden');

            $.ajax({
                type:"GET",
                url:"{{ url('/userInfo') }}/"+id,
                success: function($response){
                    if($response.status==200){
                        $("#username2").val($response.data.fullname);
                        $("#email2").val($response.data.email);
                        $("#contactNumber2").val($response.data.mobile); 
                        $("#region2").val($response.data.regionId); 
                        $("#district2").val($response.data.districtId); 
                        $("#ward2").val($response.data.wardId); 
                        $("#school2").val($response.data.schoolId); 
                        $("#adminId").val(id);   
                    }
                }
            });
        }
    </script>
@endsection