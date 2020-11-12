<?php

namespace Larangular\Categories\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Kalnoy\Nestedset\NestedSet;
use Kalnoy\Nestedset\NodeTrait;
use Larangular\Support\Traits\Validable;
use Spatie\Sluggable\SlugOptions;

/**
 * Larangular\Categories\Models\Category
 *
 * @property int                                                                    $id
 * @property string                                                                 $slug
 * @property array                                                                  $name
 * @property array                                                                  $description
 * @property int                                                                    $_lft
 * @property int                                                                    $_rgt
 * @property int                                                                    $parent_id
 * @property \Carbon\Carbon|null                                                    $created_at
 * @property \Carbon\Carbon|null                                                    $updated_at
 * @property \Carbon\Carbon|null                                                    $deleted_at
 * @property-read \Kalnoy\Nestedset\Collection|\Larangular\Categories\Models\Category[] $children
 * @property-read \Larangular\Categories\Models\Category|null                           $parent
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\Larangular\Categories\Models\Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Larangular\Categories\Models\Category whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Larangular\Categories\Models\Category whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Larangular\Categories\Models\Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Larangular\Categories\Models\Category whereLft($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Larangular\Categories\Models\Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Larangular\Categories\Models\Category whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Larangular\Categories\Models\Category whereRgt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Larangular\Categories\Models\Category whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Larangular\Categories\Models\Category whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Category extends Model {
    use NodeTrait;
    use Validable;

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    public $translatable = [
        'name',
        'description',
    ];
    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'slug',
        'name',
        'description',
        NestedSet::LFT,
        NestedSet::RGT,
        NestedSet::PARENT_ID,
    ];
    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'slug'               => 'string',
        NestedSet::LFT       => 'integer',
        NestedSet::RGT       => 'integer',
        NestedSet::PARENT_ID => 'integer',
        'deleted_at'         => 'datetime',
    ];
    /**
     * {@inheritdoc}
     */
    protected $observables = [
        'validating',
        'validated',
    ];

    /**
     * The default rules that the model will validate against.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * Whether the model should throw a
     * ValidationException if it fails validation.
     *
     * @var bool
     */
    protected $throwValidationExceptions = true;

    private $installableConfig;

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
        $this->installableConfig = app('larangular.categories.installable');
        $this->setTable($this->installableConfig->getName('categories'))
             ->setConnection($this->installableConfig->getConnection('categories'));

        $this->timestamps = $this->installableConfig->getTimestamp('categories');

        $this->setRules([
            'name'               => 'required|string|strip_tags|max:150',
            'description'        => 'nullable|string|max:32768',
            'slug'               => 'required|alpha_dash|max:150|unique:' . $this->getTable() . ',slug',
            NestedSet::LFT       => 'sometimes|required|integer',
            NestedSet::RGT       => 'sometimes|required|integer',
            NestedSet::PARENT_ID => 'nullable|integer',
        ]);
    }

    protected static function boot(): void {
        parent::boot();
        static::validating(function (Model $model) {
            if ($model->exists && $model->getSlugOptions()->generateSlugsOnUpdate) {
                $model->generateSlugOnUpdate();
            } elseif (!$model->exists && $model->getSlugOptions()->generateSlugsOnCreate) {
                $model->generateSlugOnCreate();
            }
        });
    }

    /**
     * Get all attached models of the given class to the category.
     *
     * @param string $class
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function entries(string $class): MorphToMany {
        return $this->morphedByMany($class, 'categorizable', $this->installableConfig->getName('categorizable'),
            'category_id', 'categorizable_id', 'id', 'id');
    }

    /**
     * Get the options for generating the slug.
     *
     * @return \Spatie\Sluggable\SlugOptions
     */
    public function getSlugOptions(): SlugOptions {
        return SlugOptions::create()
                          ->doNotGenerateSlugsOnUpdate()
                          ->generateSlugsFrom('name')
                          ->saveSlugsTo('slug');
    }
}
