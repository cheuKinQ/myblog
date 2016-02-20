<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Blog;
use AppBundle\Form\BlogType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @Route("/all/{page}", name="app_blog_all")
     */
    public function allAction($page)
    {
        $em = $this->getDoctrine()->getManager();
        $newPage = ($page * 6) - 6;
        $qb = $em->getRepository('AppBundle:Blog')->createQueryBuilder('Blog');
        $qb->orderBy('Blog.duedate', 'desc')
            ->setMaxResults(6)
            ->setFirstResult($newPage);
        $queryPageResult = $qb->getQuery()->getResult();
        if (!$queryPageResult) {
            $qb = $em->getRepository('AppBundle:Blog')->createQueryBuilder('Blog');
            $qb->orderBy('Blog.duedate', 'desc')
                ->setMaxResults(6)
                ->setFirstResult(($page - 1) * 6 - 6);
            $queryPageResult = $qb->getQuery()->getResult();
            return $this->render('AppBundle:Blog:all.html.twig'
                , ['queryPageResult' => $queryPageResult, 'page' => $page-1]
            );
        }
        return $this->render('AppBundle:Blog:all.html.twig'
            , ['queryPageResult' => $queryPageResult, 'page' => $page]
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
