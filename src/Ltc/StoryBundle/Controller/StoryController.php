<?php

namespace Ltc\StoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StoryController extends Controller
{
    public function featuredAction()
    {
        $story = $this->get('ltc_config.manager')->getConfig('featured_story')->getDocument()->getStory();
        if (!$story) {
            $story = $this->get('ltc_story.repository.story')->findOneBy(array());
        }

        return $this->render('LtcStoryBundle:Story:featured.html.twig', array('story' => $story));
    }

    public function viewAction($slug)
    {
        $story = $this->get('ltc_story.repository.story')->findOneBySlug($slug);
        if (!$story) {
            throw new NotFoundHttpException();
        }
        $stories = $this->get('ltc_story.repository.story')->findAllSortedByPublishedAt();

        return $this->render('LtcStoryBundle:Story:view.html.twig', array('story' => $story, 'stories' => $stories));
    }
}
