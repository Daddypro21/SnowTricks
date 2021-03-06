<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Form\TrickType;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TrickController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index( TrickRepository $trickRepository): Response
    {
        $tricks = $trickRepository->findBy([],['createdAt'=>'DESC']);

        //$this->addFlash('success','Bienvenue '.$this->getUser()->getUserIdentifier());

        return $this->render('trick/index.html.twig', ['tricks'=>$tricks]);
    }

    #[Route('/tricks/create', name: 'app_tricks_create',methods :['GET','POST'])]
    public function create(Request $request,EntityManagerInterface $em ,UserInterface $user):Response 
    {
        //User 
        $trick = new Trick;
        $form = $this->createForm(TrickType::class,$trick);
           
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $trick->setUser($user);
            $em->persist($trick);
            $em->flush();

            $this->addFlash('success','trick successfully created');

            return $this->redirectToRoute('app_home');

        }
        return $this->render('trick/create.html.twig',
        ['formulaire'=>$form->createView()]); 
    }

    #[Route('/tricks/{id<[0-9]+>}/edit', name: 'app_tricks_edit',methods:["GET","PUT","POST"])]
    public function edit(Trick $trick,Request $request,EntityManagerInterface $em):Response 
    {
        $form = $this->createForm(TrickType::class,$trick);
    
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em->flush();

            $this->addFlash('success','trick successfully updated');

            return $this->redirectToRoute('app_home');

        }
        return $this->render('trick/edit.html.twig',[
            'formulaire'=>$form->createView(),
            'trick'=>$trick
        ]);
    }

    #[Route('/tricks/{id<[0-9]+>}/delete', name: 'app_tricks_delete',methods :["GET","POST"])]
    public function delete(Trick $trick,Request $request ,EntityManagerInterface $em):Response 
    {
        if($this->isCsrfTokenValid('trick_delete_'.$trick->getId(),$request->request->get('csrf_token'))){
            $em->remove($trick);
            $em->flush();
            $this->addFlash('info','trick succesfully deleted');
        }
        return $this->redirectToRoute('app_home');
    }

    #[Route('/tricks/{id<[0-9]+>}', name: 'app_tricks_show',methods :['GET'])]
    public function show(Trick $trick,TrickRepository $repo): Response 
    {
        //$trick = $repo->find($id);
        if(! $trick){
            throw $this->createNotFoundException("Ce trick n'existe pas ");
        }
        return $this->render("trick/show.html.twig",compact('trick'));
    }
}
