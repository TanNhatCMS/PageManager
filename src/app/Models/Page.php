<?php

namespace Backpack\PageManager\app\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Artesaos\SEOTools\Facades\JsonLdMulti;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\TwitterCard;

use Ophim\Core\Contracts\HasUrlInterface;

use Hacoidev\CachingModel\Contracts\Cacheable;
use Hacoidev\CachingModel\HasCache;
use Ophim\Core\Contracts\SeoInterface;
use Ophim\Core\Traits\HasTitle;
use Ophim\Core\Traits\HasDescription;
use Ophim\Core\Traits\HasKeywords;
use Ophim\Core\Contracts\TaxonomyInterface;

class Page extends Model implements TaxonomyInterface, Cacheable, SeoInterface
{
    use CrudTrait;
    use Sluggable;
    use SluggableScopeHelpers;
    use HasCache;
    use HasTitle;
    use HasDescription;
    use HasKeywords;

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
    public function getUrl()
    {
          return getPageLink();
    }
    public function generateSeoTags()
    {
        $seo_title = $this->getTitle();
        $seo_des = Str::limit($this->getDescription(), 150, '...');
        $seo_key = $this->getKeywords();

        SEOMeta::setTitle($seo_title, false)
            ->setDescription($seo_des)
            ->addKeyword($seo_key )
            ->setCanonical($this->getUrl())
            //->setPrev(request()->root())
            ->setPrev(request()->root());
        // ->addMeta($meta, $value, 'property');

        OpenGraph::setSiteName(setting('site_meta_siteName'))
            ->setTitle($seo_title, false)
            ->addProperty('type', 'movie')
            ->addProperty('locale', 'vi-VN')
            //->addProperty('updated_time', $this->updated_at)
            ->addProperty('url', $this->getUrl())
            ->setDescription($seo_des);
           // ->addImages([request()->root() . $this->thumb_url, request()->root() . $this->poster_url]);

        TwitterCard::setSite(setting('site_meta_siteName'))
            ->setTitle($seo_title, false)
            ->setType('movie')
            //->setImage(request()->root() . $this->thumb_url)
            ->setDescription($seo_des)
            ->setUrl($this->getUrl());
        // ->addValue($key, $value);

        JsonLdMulti::newJsonLd()
            ->setSite(setting('site_meta_siteName'))
            ->setTitle($seo_title, false)
            ->setType('movie')
            ->setDescription($seo_des)
           // ->setImages([request()->root() . $this->thumb_url, request()->root() . $this->poster_url])
           // ->addValue('director', count($this->directors) ? $this->directors()->first()->name : "")
            ->setUrl($this->getUrl());
        // ->addValue($key, $value);
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
