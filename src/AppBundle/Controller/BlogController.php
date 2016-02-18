<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Blog;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Class BlogController
 * @package AppBundle\Controller
// * @Route("/blog")
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
     * @Route("/all",name="app_blog_all")
     */
    public function allAction()
    {
        $em = $this->getDoctrine()->getManager();
        $all = $em->getRepository('AppBundle:Blog')->findAll();
        return $this->render('AppBundle:Blog:all.html.twig'
            ,array('all' => $all)
        );
    }

    /**
     * @Route("/report",name="app_blog_report")
     */
    public function reportAction(Request $request)
    {
        $blog = new Blog();

        $form = $this->createFormBuilder($blog)
            ->add('title',TextType::class, ['label' => '标题: '])
            ->add('content',TextType::class,['attr' => ['class' => 'ceshi']
            , 'label' => ' '])
            ->add('submit', SubmitType::class, ['label' => '发博文'])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $blog->setUsername("测试");
            $em->persist($blog);
            $em->flush();
            return $this->redirectToRoute("app_blog_all");
        }
        return $this->render('AppBundle:Blog:report.html.twig', array('formm' => $form->createView()));
    }
}
