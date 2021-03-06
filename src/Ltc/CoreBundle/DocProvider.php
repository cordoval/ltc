<?php

namespace Ltc\CoreBundle;

use Ltc\BlogBundle\Document\BlogEntryRepository;
use Ltc\ArticleBundle\Document\ArticleRepository;
use Ltc\ArticleBundle\Document\CategoryRepository;
use Ltc\DocBundle\Document\Doc;

/**
 * Provides all docs, blog entries and articles
 */
class DocProvider
{
    /**
     * @var BlogEntryRepository
     */
    protected $blogEntryRepository = null;

    /**
     * @var ArticleRepository
     */
    protected $articleRepository = null;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository = null;

    /**
     * @param BlogEntryRepository blogEntryRepository
     * @param ArticleRepository articleRepository
     * @param CategoryRepository categoryRepository
     */
    public function __construct(BlogEntryRepository $blogEntryRepository, ArticleRepository $articleRepository, CategoryRepository $categoryRepository)
    {
        $this->blogEntryRepository = $blogEntryRepository;
        $this->articleRepository   = $articleRepository;
        $this->categoryRepository  = $categoryRepository;
    }

    /**
     * Gets all docs
     *
     * @return array
     **/
    public function getPublishedDocsSortByPublishedAt($limit)
    {
        $blogEntries = $this->blogEntryRepository->findPublished()->toArray();
        $articles    = $this->articleRepository->findPublished()->toArray();

        $docs = array_merge($blogEntries, $articles);

        usort($docs, function(Doc $a, Doc $b) {
            return $a->getPublishedAt() < $b->getPublishedAt();
        });

        return array_slice($docs, $limit);
    }

    /**
     * Gets published articles by category
     *
     * @return array
     **/
    public function getPublishedArticlesCategorized()
    {
        $categories = array();
        foreach ($this->categoryRepository->findAll() as $category) {
            $categories[$category->getSlug()] = array(
                'category' => $category,
                'articles' => $this->articleRepository->findPublishedByCategory($category)
            );
        }

        return $categories;
    }
}
