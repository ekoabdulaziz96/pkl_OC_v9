<?php

namespace App\Http\Controllers\Admin;
use Validator;
use App\User;
use App\Cabang;
use Illuminate\Http\Request;
use Mockery\Exception;
use Yajra\DataTables\DataTables;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Events\Auth\UserActivationEmail;
use App\Http\Controllers\Controller;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cabangs = Cabang::where('slug','<>','-')->where('slug','<>','super_admin')->orderBy('nama', 'asc')->get();
        return view('admin.user',compact('cabangs'));
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
         $input = $request->all();
          $rules = array(
           'nama' => 'required|string|max:20',
          'email' => 'required|email',
          'status' => 'required',
        );
         $validator = Validator::make( $input, $rules);
            if ($validator->fails()){
                  return response()->json([
                    'success' => true,
                    'message' => 'Silahkan lengkapi form yg wajib diisi, perhatkan format email dan nama maks 20 karakter',
                    'title'=> 'Mohon Diperhatikan!',
                    'type'=> 'warning',
                    'timer'=> 2500
                ]);
            }else if(User::where('email',$request->email)->exists() ){
           return response()->json([
                'success' => true,
                'message' => 'maaf, email user "'.$request->email.'" sudah ada. Silahkan input dengan email berbeda',
                'title'=> 'Gagal Menambahkan!',
                'type'=> 'warning',
                'timer'=> 5000
            ]);
        }else  {
            $input = $request->except(['password']);
            
            $input['cabang_id'] = 1;
            $input['wilayah'] = 0;
            if ($request->has('cabang_id')){
                $input['cabang_id'] = $request->cabang_id;
                $input['wilayah'] = $request->wilayah;
            }
  
            $input['password'] = bcrypt($request->password);
            $input['active'] = false;
            $input['activation_token'] = str_random(255);

            if ($request->hasFile('foto')){
                $input['foto'] = '/upload/foto/'.$user->id.str_slug($input['nama'], '-').'.'.$request->foto->getClientOriginalExtension();
                $request->foto->move(public_path('/upload/foto/'), $input['foto']);
            }
            $user = User::create($input);
            event (new UserActivationEmail($user));

            return response()->json([
                'success' => true,
                'message' => 'Data User baru berhasil ditambahkan, dan email aktivasi berhasil dikirim',
                'title'=> 'Sukses Menambahkan!',
                'type'=> 'success',
                'timer'=> 2500
            ]);
        }    
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        
        $user = User::findOrFail($user->id);
        // $user['src_foto'] = "{{  asset('".$user->foto."') }}";

       // console.log($user);
        return $user;
    }    

    /**
     * 
     *

     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function cabang($id)
    {
        $cabang = Cabang::where('id',$id)->first();
        return $cabang;
    }    

    /**
     * 
     *

     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function foto($id)
    {
        $user = User::findOrFail($id);
        if ($user->foto != '-'){
            unlink(public_path($user->foto));
        }
         $input['foto'] = '-';
         $user->update($input);
         return response()->json([
                'success' => true,
                'message' => 'Foto Berhasil di Reset',
                'title'=> 'Sukses!',
                'type'=> 'success',
                'timer'=> 2500
         ]);
    }    
    /**
     * 
     *

     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function password($id)
    {
        $user = User::findOrFail($id);
        $input['password'] = bcrypt('onecareindonesia');
        $user->update($input);
         return response()->json([
                'success' => true,
                'message' => 'Password Berhasil di Reset menjadi: "onecareindonesia"',
                'title'=> 'Sukses!',
                'type'=> 'success',
                'timer'=> 2500
         ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $input = $request->all();
          $rules = array(
          'nama' => 'required|string|max:20',
          'email' => 'required|email',
          'status' => 'required',
        );
             $validator = Validator::make( $input, $rules);
        if ($validator->fails()){
                  return response()->json([
                    'success' => true,
                    'message' => 'Silahkan lengkapi form yg wajib diisi, perhatkan format email dan nama maks 20 karakter',
                    'title'=> 'Mohon Diperhatikan!',
                    'type'=> 'warning',
                    'timer'=> 2500
                ]);
        }else if(User::where('email',$request->email)->where('id','<>',$user->id)->exists() ){
           return response()->json([
                'success' => true,
                'message' => 'maaf, email user "'.$request->email.'" sudah ada. Silahkan input dengan email berbeda',
                'title'=> 'Gagal Menambahkan!',
                'type'=> 'warning',
                'timer'=> 5000
            ]);
        }else  {
            $input = $request->except(['password']);

            $input['cabang_id'] = 1;
            $input['wilayah'] = 0;
            if ($request->has('cabang_id')){
                $input['cabang_id'] = $request->cabang_id;
                $input['wilayah'] = $request->wilayah;
            }

            $user = User::findOrFail($user->id);

            $input['foto'] = $user->foto;

            if ($request->hasFile('foto')){
                if ($user->foto != '-'){
                    unlink(public_path($user->foto));
                }
                $input['foto'] = '/upload/foto/'.$user->id.str_slug($input['nama'], '-').'.'.$request->foto->getClientOriginalExtension();
                $request->foto->move(public_path('/upload/foto/'), $input['foto']);
            }

            if($user->email != $request->email){
                $input['active'] = false;
                $input['activation_token'] = str_random(255);
            }
            
            $user->update($input);
            if($user->active == false){
                event (new UserActivationEmail($user));
            }

            return response()->json([
                'success' => true,
                'message' => 'Data User berhasil diperbarui',
                'title'=> 'Sukses Memperbarui!',
                'type'=> 'success',
                'timer'=> 2500
            ]);
        }    
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user = User::findOrFail($user->id);
        if ($user->foto != '-'){
            unlink(public_path($user->foto));
        }
        User::destroy($user->id);
        return response()->json([
            'success' => true,
            'message' => 'Data User berhasil dihapus',
            'title'=> 'Sukses Menghapus!',
            'type'=> 'success',
            'timer'=> 2500
        ]);
    }

     public function apiUser()
    {

        $user = User::orderBy('cabang_id','asc')->orderBy('wilayah','asc')->orderBy('status','asc');

        return Datatables::of($user)
            ->addColumn('nomor', function(){
                    global $nomor;
                    return ++$nomor;
            }) ->addColumn('cawil', function($user){
                    $cabang = Cabang::findOrFail($user->cabang_id);
                    if ($user->cabang_id == 1){
                        return $cabang->nama;
                    }else{
                        return $cabang->nama.'  ('.$user->wilayah.')';
                    }
            }) 
           ->addColumn('reset', function($user){
            
            return '<div align="center" >' .
               '<a onclick="resetPasswordUser('. $user->id .')" style="background-color: #B1EDF6"  class="btn btn-default btn-sm btn-flat" data-toggle="tooltip" data-placement="top" title="Reset Password: onecareindonesia"><i class="glyphicon glyphicon-erase"></i></a> ' . 
               '<a onclick="resetFotoUser('. $user->id .')" style="background-color: #B1EDF6" class="btn btn-default btn-sm btn-flat" data-toggle="tooltip" data-placement="top" title="Reset Foto"><i class="glyphicon glyphicon-picture"></i></a> ' .
               '</div>';
                   

            }) ->addColumn('action', function($user){
            if ($user->status =='super_admin' && $user->id <= 2){
                return '<div align="center" >' .
                   '<a onclick="showUser('. $user->id .')" class="btn btn-info btn-sm" data-toggle="tooltip" data-placement="top" title="Detail"><i class="glyphicon glyphicon-eye-open"></i></a> ' . 
                   '<a onclick="editUser('. $user->id .')" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Edit"><i class="glyphicon glyphicon-edit"></i></a> ' .
                   '<a href="#" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Hapus"><i class="glyphicon glyphicon-trash"></i></a>'.
                   '</div>';

            }else {
                return '<div align="center" >' .
                   '<a onclick="showUser('. $user->id .')" class="btn btn-info btn-sm" data-toggle="tooltip" data-placement="top" title="Detail"><i class="glyphicon glyphicon-eye-open"></i></a> ' . 
                   '<a onclick="editUser('. $user->id .')" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Edit"><i class="glyphicon glyphicon-edit"></i></a> ' .
                   '<a onclick="deleteUser('. $user->id .')" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Hapus"><i class="glyphicon glyphicon-trash"></i></a>'.
                   '</div>';
                
            }

            })
            ->rawColumns(['nomor', 'action','cawil','reset'])->make(true);
    }
}
