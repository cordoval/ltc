<?php

namespace Ltc\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Ltc\BlogBundle\Form\BlogEntryForm;
use Ltc\BlogBundle\Document\BlogEntry;
use Ltc\BlogBundle\Form\BlogEntryFormType;

class BlogAdminController extends Controller
{
    public function indexAction()
    {
        $blogEntries = $this->get('ltc_blog.repository.blog_entry')->findAll();

        return $this->render('LtcBlogBundle:Admin:index.html.twig', array(
            'objects' => $blogEntries
        ));
    }

    public function newAction()
    {
        $blogEntry = new BlogEntry();
        $form = $this->get('form.factory')->create(new BlogEntryFormType(), $blogEntry);
        if ('POST' === $request->getMethod()) {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $this->get('doctrine.odm.mongodb.document_manager')->persist($blogEntry);
                $this->save();

                return new RedirectResponse($this->get('router')->generate('ltc_blog_admin_entry_list'));
            }
        }

        return $this->render('LtcBlogBundle:Admin:new.html.twig', array(
            'doc' => $blogEntry,
            'form' => $form->createView()
        ));
    }

    public function editAction($slug)
    {
        $blogEntry = $this->get('ltc_blog.repository.blog_entry')->findOneBySlug($slug);
        if (!$blogEntry) throw new NotFoundHttpException();
        $form = $this->get('form.factory')->create(new BlogEntryFormType(), $blogEntry);
        $request = $this->get('request');
        if ('POST' === $request->getMethod()) {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $this->save();

                return new RedirectResponse($this->get('router')->generate('ltc_blog_admin_entry_list'));
            }
        }

        return $this->render('LtcBlogBundle:Admin:edit.html.twig', array(
            'doc' => $blogEntry,
            'form' => $form->createView()
        ));
    }

    public function deleteAction($id)
    {
        $entry = $this->get('ltc_blog.repository.blog_entry')->find($id);
        $this->get('doctrine.odm.mongodb.document_manager')->remove($entry);
        $this->get('doctrine.odm.mongodb.document_manager')->flush();
        $this->get('ltc_tag.denormalizer')->denormalize();
        $this->get('doctrine.odm.mongodb.document_manager')->flush();
        $this->get('session')->setFlash('notice', 'Billet supprime');

        return new RedirectResponse($this->get('router')->generate('ltc_blog_admin_entry_list'));
    }

    protected function save()
    {
        $this->get('doctrine.odm.mongodb.document_manager')->flush();
        $this->get('ltc_tag.denormalizer')->denormalize();
        $this->get('doctrine.odm.mongodb.document_manager')->flush();
        $this->get('session')->setFlash('notice', 'Modifications enregistrees');
    }
}
