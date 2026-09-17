<?php

// app/Datas/RatingDataCollection.php

declare(strict_types=1);

namespace AndyDefer\LaravelRatings\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\LaravelRatings\Datas\RatingData;

/**
 * @extends AbstractTypedCollection<RatingData>
 */
final class RatingDataCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(RatingData::class);
    }
}
