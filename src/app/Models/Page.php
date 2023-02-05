<?php

namespace Backpack\PageManager\app\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

use Artesaos\SEOTools\Facades\SEOTools;


class Page extends Model
{
    use CrudTrait;
    use Sluggable;
    use SluggableScopeHelpers;



    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'pages';
    protected $primaryKey = 'id';
    public $timestamps = true;
    // protected $guarded = ['id'];
    protected $fillable = ['template', 'name', 'title', 'slug', 'content', 'extras'];
    // protected $hidden = [];
    // protected $dates = [];
    protected $fakeColumns = ['extras'];
    protected $casts = [
        'extras' => 'array',
    ];

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'slug_or_title',
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    public function generateSeoTags($seo_title, $des, $seo_key)
    {
        $seo_des = Str::limit( $des, 150, '...');
        SEOTools::setTitle($seo_title, false);
        SEOTools::setDescription($seo_des);
        SEOTools::setCanonical($this->getPageLink());
        SEOTools::metatags()->addKeyword($seo_des);
        SEOTools::metatags()->setPrev(request()->root());
        SEOTools::opengraph()->setSiteName(setting('site_meta_siteName'));
        SEOTools::opengraph()->addProperty('type', 'movie');
        SEOTools::opengraph()->addProperty('locale', 'vi-VN');
        SEOTools::opengraph()->addProperty('url', $this->getPageLink());
        SEOTools::twitter()->setSite(setting('site_meta_siteName'));
        SEOTools::twitter()->setUrl($this->getPageLink());
        SEOTools::twitter()->setType('movie');
        SEOTools::jsonLd()->setSite(setting('site_meta_siteName'));
        SEOTools::jsonLd()->setType('movie');
        SEOTools::jsonLd()->setUrl($this->getPageLink());
    }

    protected function descriptionPattern(): string
    {
        return Setting::get('site.title');
    }

    public function getDescription(): string
    {
        $pattern = $this->descriptionPattern();

        preg_match_all('/{.*?}/', $pattern, $vars);

        foreach ($vars[0] as $var) {
            try {
                $x = str_replace('{', '', $var);
                $x = str_replace('}', '', $x);
                $keys = explode('.', (string) $x);
                $data = $this;
                foreach ($keys as $key) {
                    $data = $data->{$key};
                }
                $pattern = str_replace($var, $data, $pattern);
            } catch (\Exception $e) {
            }
        }

        return $pattern;
    }
    protected function keywordsPattern(): string
    {
        return Setting::get('site.title');
    }

    public function getKeywords(): string
    {
        $pattern = $this->keywordsPattern();

        preg_match_all('/{.*?}/', $pattern, $vars);

        foreach ($vars[0] as $var) {
            try {
                $x = str_replace('{', '', $var);
                $x = str_replace('}', '', $x);
                $keys = explode('.', (string) $x);
                $data = $this;
                foreach ($keys as $key) {
                    $data = $data->{$key};
                }
                $pattern = str_replace($var, $data, $pattern);
            } catch (\Exception $e) {
            }
        }

        return $pattern;
    }

    protected function titlePattern(): string
    {
        return Setting::get('site.title');
    }

    public function getTitle(): string
    {
        $pattern = $this->titlePattern();

        preg_match_all('/{.*?}/', $pattern, $vars);

        foreach ($vars[0] as $var) {
            try {
                $x = str_replace('{', '', $var);
                $x = str_replace('}', '', $x);
                $keys = explode('.', (string) $x);
                $data = $this;
                foreach ($keys as $key) {
                    $data = $data->{$key};
                }
                $pattern = str_replace($var, $data, $pattern);
            } catch (\Exception $e) {
            }
        }

        return $pattern;
    }
    public function getTemplateName()
    {
        return trans('backpack::pagemanager.'.$this->template);
    }

    public function getPageLink()
    {
        return url("page/".$this->slug);
    }

    public function getOpenButton()
    {
        return '<a class="btn btn-sm btn-link" href="'.$this->getPageLink().'" target="_blank">'.
            '<i class="la la-eye"></i> '.trans('backpack::pagemanager.open').'</a>';
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESORS
    |--------------------------------------------------------------------------
    */

    // The slug is created automatically from the "name" field if no slug exists.
    public function getSlugOrTitleAttribute()
    {
        if ($this->slug != '') {
            return $this->slug;
        }

        return $this->title;
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
