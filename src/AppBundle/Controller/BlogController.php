<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Blog;
use AppBundle\Form\BlogType;
use AppBundle\Form\BlogEditType;
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
    public function allAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $dql = 'SELECT a FROM AppBundle:Blog a';
        $query = $em->createQuery($dql);
        $paginator = $this->get('knp_paginator');
//        if (!$queryPageResult) {
//            $qb = $em->getRepository('AppBundle:Blog')->createQueryBuilder('Blog');
//            $qb->orderBy('Blog.duedate', 'desc')
//                ->setMaxResults(6)
//                ->setFirstResult(($page - 1) * 6 - 6);
//            $queryPageResult = $qb->getQuery()->getResult();
//            return $this->render('AppBundle:Blog:all.html.twig'
//                , ['queryPageResult' => $queryPageResult, 'page' => $page-1]
//            );
//        }
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            3/*limit per page*/
        );

        // parameters to template
        return $this->render('AppBundle:Blog:all.html.twig', array('pagination' => $pagination));
//        return $this->render('AppBundle:Blog:all.html.twig'
//            , ['queryPageResult' => $queryPageResult, 'page' => $page]
//        );
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

    /**
     * 详情列表
     * @Route("/oneArticle/{id}",name="app_blog_oneArticle")
     */
    public function oneArticleAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $article = $em->getRepository('AppBundle:Blog')->findOneBy(['id' => $id]);
        $sql = $em->getRepository('AppBundle:Blog')->createQueryBuilder('Blog');
        $sql->orderBy('Blog.duedate','desc')
            ->setMaxResults(3)
            ->setFirstResult(0);
        $recentArticle = $sql->getQuery()->getResult();
        if (!$article || !$recentArticle) {
            return $this->redirectToRoute('app_blog_all');
        }
        return $this->render('AppBundle:Blog:oneArticle.html.twig', ['article' => $article,'recentArticle' => $recentArticle]);
    }

    /**
     * 编辑文章
     * @Route("/edit/{id}",name="app_blog_edit")
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $article = $em->getRepository('AppBundle:Blog')->findOneBy(['id' => $id]);
        if (!$article) {
            return $this->redirectToRoute('app_blog_all');
        }
        $blog = new Blog();
        //生成 发表博文表单
        $blog->setTitle($article->getTitle())->setContent($article->getContent());
        $form = $this->createForm(BlogEditType::class, $blog);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $article->setTitle($blog->getTitle())->setContent($blog->getContent())->setDuedate(new \DateTime());
            $em->flush();
            return $this->redirectToRoute("app_blog_all");
        }
        return $this->render('AppBundle:Blog:edit.html.twig', ['form' => $form->createView(), 'article' => $article]);
    }

    /**
     * 删除条目
     * @Route("/delete/{id}",name="app_blog_delete")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $article = $em->getRepository('AppBundle:Blog')->findOneBy(['id' => $id]);
        if (!$article) {
            return $this->redirectToRoute('app_blog_all');
        }
        $em->remove($article);
        $em->flush();
        return $this->redirectToRoute('app_blog_all');
    }

    /**
     * @Route("/search",name="app_blog_search")
     */
    public function searchAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $info = $request->get('info');
        $dql = $em->createQuery("select a from AppBundle:Blog a where a.title like :title order by a.duedate desc")
                ->setParameter('title',"%$info%");
        $query = $dql->getResult();
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            3/*limit per page*/
        );
        return $this->render('AppBundle:Blog:all.html.twig', ['pagination' => $pagination]);
    }
}
