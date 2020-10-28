<?php

namespace App\Http\Controllers;

use App\Models\User;

use App\Rules\MatchOldPassword;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{

    public function jobseekerReg(){
        return view('auth.jobseeker-reg');
    }

    public function updatePassword(Request $request){

        $request->validate([
            'password' => [
                'required',
                'string',
                'confirmed',
                'min:6',             // must be at least 10 characters in length
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character
            ],
            'current_password' => ['required', new MatchOldPassword],
        ]);

        User::find(auth()->user()->id)->update(['password'=> Hash::make($request->password)]);

       return back()->with('success_message','Password succesfully changed');
    }

    public function editSummary()
    {
        return view('pages.edit_objective_det');
    }

    public function roleLogout(){
        $role = Auth::user()->role;
        Session::put('last_role', Auth::user()->role);

        if(Auth::user()->hasRole('employer')){
            $role = 'job seeker';
            $url = '/jobseeker/login';
        }else{
            $role = 'employer';
            $url = '/employer/login';
        }
        Auth::logout();
        return redirect($url)->with(['role_error' => 'Login with your '.$role.' account']);
    }

    public function employerReg(){
        return view('auth.employer.register');

    }

    public function employerLogin(){
        return view('auth.employer.login');
    }
    public function jobseekerLogin(){
        return view('auth.jobseeker.login');
    }

    public function save(Request $request){
        return $request;
    }

    public function checkUser($type, Request $request){
        $q = $request->get('q');
        if($type === 'email'){
            $user = User::whereEmail($q)->first();
            if($user){
                return 0;
            }else{
                return 1;
            }
        }else{
            $user = User::whereUsername($q)->first();
            if($user){
                return 0;
            }else{
                return 1;
            }
        }
    }

    public function updateUser(Request $request)
    {
        $user = User::findOrFail(Auth::id());

        $user->update(['availability' => $request->availability]);

        $request->session()->flash('message', 'Your Availability Status Was Successfully Updated');
        $request->session()->flash('message-type', 'success');
        return redirect()->route('dashboard');

    }

    public function updateProfile(Request $request)
    {
        $data = $request->all();
        if($request->has('skills')){
            $data['skills'] = explode (",", $data['skills']);
        }
        if($request->file('cv'))
        {
            $file = $request->file('cv');
            $filename = time() . '.' . $request->file('cv')->extension();
            $filePath = '/files/uploads/cv/';
            if (!file_exists(public_path($filePath))) {
                mkdir(public_path($filePath), 0755, true);
            }
            $file->move(public_path($filePath), $filename);
            $data['cv'] = $filePath.$filename;
        }
        $user = User::findOrFail(auth()->user()->id);
        $user->update($data);

        return redirect()->back()->with('success','Profile successfully updated');

    }

    public function getUnreadNots(){
        return auth()->user()->unreadNotifications;
    }

    public function notifications(){
        auth()->user()->unreadNotifications->markAsRead();
        $nots = auth()->user()->notifications;
        return view('dashboard.notifications', compact('nots'));
    }

    public function getRoles(){
        $roles = Role::where('name','!=','dev')->get();
        $results[] = [
                'id'   => '',
                'text' => __('voyager::generic.none'),
        ];
        foreach ($roles as $item) {
            $results[] = [
                'id'   => $item->id,
                'text' => $item->display_name,
            ];
        }
        return response()->json([
            'results'    => $results,
            'pagination' => [
//                'more' => ($total_count > ($skip + $on_page)),
            ],
        ]);
    }

    public function edit(Request $request, $id)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        if (strlen($dataType->model_name) != 0) {
            $model = app($dataType->model_name);

            // Use withTrashed() if model uses SoftDeletes and if toggle is selected
            if ($model && in_array(SoftDeletes::class, class_uses_recursive($model))) {
                $model = $model->withTrashed();
            }
            if ($dataType->scope && $dataType->scope != '' && method_exists($model, 'scope'.ucfirst($dataType->scope))) {
                $model = $model->{$dataType->scope}();
            }
            $dataTypeContent = call_user_func([$model, 'findOrFail'], $id);
        } else {
            // If Model doest exist, get data from table name
            $dataTypeContent = DB::table($dataType->name)->where('id', $id)->first();
        }

        foreach ($dataType->editRows as $key => $row) {
            $dataType->editRows[$key]['col_width'] = isset($row->details->width) ? $row->details->width : 100;
        }

        // If a column has a relationship associated with it, we do not want to show that field
        $this->removeRelationshipField($dataType, 'edit');

        // Check permission
        $this->authorize('edit', $dataTypeContent);

        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($dataTypeContent);

        $view = 'voyager::bread.edit-add';


        if (view()->exists("voyager::$slug.edit-add")) {
            $view = "voyager::$slug.edit-add";
        }

        return Voyager::view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable'));
    }

    public function store(Request $request){
        Validator::make($request, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required']
        ])->validate();

        return User::create([
            'username' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
        ]);
    }
}
