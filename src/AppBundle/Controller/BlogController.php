<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Blog;
use AppBundle\Form\BlogType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BlogController
 * @package AppBundle\Controller
 * @Route("/blog")
 */
class BlogController extends Controller
{
    /**
     * @Route("/index",name="app_blog_index")
     */
    public function indexAction()
    {
        return $this->render('AppBundle:Blog:index.html.twig');
    }

    /**
     * @Route("/all", name="app_blog_all")
     */
    public function allAction()
    {
        $em = $this->getDoctrine()->getManager();
        $all = $em->getRepository('AppBundle:Blog')->findAll();
        return $this->render('AppBundle:Blog:all.html.twig'
            , array('all' => $all)
        );
    }

    /**
     * @Route("/report",name="app_blog_report")
     */
    public function reportAction(Request $request)
    {
        $blog = new Blog();
        //生成 发表博文表单
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $username = $blog->setUsername($name = $this->getUser()->getUsername());
            $em->persist($username);
            $em->flush();
            return $this->redirectToRoute("app_blog_all");
        }
        return $this->render('AppBundle:Blog:report.html.twig', array('form' => $form->createView()));
    }
}
