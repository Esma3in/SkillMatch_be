<?php

namespace App\Models;

use App\Models\Tool;
use App\Models\Skill;
use App\Models\Candidate;
use App\Models\Prerequiste;
use App\Models\SkillRoadmap;
use App\Models\CandidateCourse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Roadmap extends Model
{
    use HasFactory;
    protected $fillable=[
        'skill_id',
        'name',
        'completed',
        'candidate_id',
        'company_id'
    ];
    // get the candidate correspnonding to this roadmap
    public function Candidate(){
        return $this->belongsTo(Candidate::class);
    }
    // get the skills which will be used to generate this roadmap
    public function Skills(){
        return $this->hasMany(Skill::class);
    }
    public function prerequsite(){
        return $this->hasMany(Prerequiste ::class);
    }
    public function CandidateCourse(){
        return $this->hasMany(CandidateCourse::class);
    }
    public function RoadmapSkill(){
        return $this->hasMany(SkillRoadmap::class);
    }
    public function Tools(){
        return $this->hasMany(Tool::class);
    }
    // qcm for roadmap
    public function QcmForRoadmap(){
        return $this->belongsToMany(QcmForRoadmap::class);
    }
    public function Company(){
        return $this->belongsTo(Company::class);
    }
}
