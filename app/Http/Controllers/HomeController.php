<?php

namespace App\Http\Controllers;

use App\Mail\MailEmployer;
use App\Mail\SendJobMail;
use App\Models\City;
use App\Models\Company;
use App\Models\EmployerProduct;
use App\Models\FunctionalArea;
use App\Models\IndustryType;
use App\Models\Job;
use App\Models\Page;
use App\Models\Products;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;


class HomeController extends Controller
{
    public function mailJob(Request $request){
        $mail = $request->all();
        Mail::to($request->fmail)->send(new SendJobMail($mail));
        $message = 'Mail successfully sent to Your Friend';
        return back()->with('success',$message);
    }

    public function purchasePlan(Request $request){
        if(!$request->has('job_posting_id') && !$request->has('db_access_id')){
            return redirect()->back()->with('failure', 'Pls select a package');
        }
        if($request->has('job_posting_id') && $request->has('db_access_id')){
            return 'all';

        }elseif($request->has('job_posting_id')){
            $product = $request->job_posting_id;
            $p = Products::findOrFail($product);
            if($p->price < 1){
                return redirect()->back()->with('failure', "You can't subscribe to free plan");
            }
            $expired_at = Carbon::now()->addDay($p->no_of_days);
            EmployerProduct::create([
                'user_id' => auth()->id(),
                'product_id' => $product,
                'expired_at' => $expired_at,
            ]);

        }else{
            $db_access = $request->db_access_id;
            return 'db';
        }
        return $request->all();
    }

    public function mailEmployer(Request $request){
        $mail = $request->all();
        Mail::to($request->mail)->send(new MailEmployer($mail));
        $message = 'Mail successfully sent to Recruiter';
        return back()->with('success',$message);
    }
    public function index()
    {
        $cities = Cache::remember('cities', 3600, function () {
            return City::withCount('jobs')->whereFeatured(1)->get();
        });
        $companies = Company::select('name','logo','slug')->limit('18')->get();
        $f_areas = FunctionalArea::withCount('jobs')->whereFeatured(1)->inRandomOrder()->limit(6)->get();
        $industries = IndustryType::withCount('jobs')->orderBy('jobs_count', 'desc')->limit(12)->get();
        $jobs = Job::latest()->limit(8)->get();
        return view('index', compact('f_areas','industries','companies','jobs','cities'));
    }

    public function terms(){
        $content = Page::whereTitle('Seeker : Terms and Conditions')->first()->content;
        return view('pages.terms',compact('content'));
    }

    public function about(){
        $content = Page::whereTitle('Seeker : About Us')->first()->content;
        return view('pages.about',compact('content'));
    }

    public function privacy(){
        $content = Page::whereTitle('Seeker : Privacy Policy')->first()->content;
        return view('pages.privacy',compact('content'));
    }

    public function faq(){
        $content = Page::whereTitle('Seeker : FAQ')->first()->content;
        return view('pages.faq',compact('content'));
    }

    public function recruitersProfile($username){
        $user = User::whereUsername($username)->first();
        $top_recruiters = User::with('company')->whereRoleIs('employer')->get();
        return view('pages.recruiter-profile', compact('top_recruiters','user'));
    }

    public function contactus(){

        $content = Page::whereTitle('Contact Us')->first()->content;

        return view('pages.contactus', compact('content'));
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
//    public function index()
//    {
//
//        return view('home');
//    }
}
