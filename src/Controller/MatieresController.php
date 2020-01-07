<?php

namespace App\Controller;

use App\Entity\Matieres;
use App\Form\MatieresType;
use App\Entity\User;
use App\Repository\MatieresRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/matieres")
 */
class MatieresController extends AbstractController
{
    /**
     * @Route("/", name="matieres_index", methods={"GET"})
     */
    public function index(MatieresRepository $matieresRepository): Response
    {
       
            return $this->render('matieres/index.html.twig', [
                'matieres' => $matieresRepository->findAll(),
            ]);
    
        
    }
    /**
     * @Route("/matiere", name="matieres_matiere", methods={"GET"})
     */
    public function matiere(MatieresRepository $matieresRepository): Response
    {
       
        if(in_array('ROLE_USER_ETUDIANT', $this->getUser()->getRoles()))
        {
            
            return $this->render('matieres/index.html.twig', [
                'matieres' => $matieresRepository->findBy(
                 ["classe"=>$this->getUser()->getNiveau()]),
  
                 
            ]);
        }
        
    }
    /**
     * @Route("/matieres", name="matieres_matieres_prof", methods={"GET"})
     */
    public function matieres_prof(MatieresRepository $matieresRepository): Response
    {
       
        if(in_array('ROLE_USER_PROF', $this->getUser()->getRoles()))
        {
            return $this->render('matieres/index.html.twig', [
                'matieres' => $matieresRepository->findBy(
                 ["professeur"=>$this->getUser()->getNom()]),
            ]);
        }else{
            return $this->render('matieres/index.html.twig', [
                'matieres' => $matieresRepository->findAll(),
            ]);
        }
        
    }
    /**
     * @Route("/etudiant", name="matieres_etudiant", methods={"GET"})
    */
    public function matiere_etudiant(MatieresRepository $matieresRepository,Request $request): Response
    {
        
        if(in_array('ROLE_ADMIN', $this->getUser()->getRoles()))
        {
            if ($_GET['classe']=="prof") {
                return $this->render('matieres/index.html.twig', [
                    'matieres' => $matieresRepository->findBy(
                    ["professeur"=>$_GET['nom']])
                ]);
            }
            else {
                return $this->render('matieres/index.html.twig', [
                    'matieres' => $matieresRepository->findBy(
                    ["classe"=>$_GET['classe']])
                ]);
            }
            
        }
        
    }

    /**
     * @Route("/new", name="matieres_new", methods={"GET","POST"})
     */
    public function new(Request $request,UserRepository $listes,MatieresRepository $matieres): Response
    {  
        //$em = $this->get('doctrine')->getManager()->createQueryBuilder();;
        $listess=array();
        $listess= $listes->findBy(
            ["niveau" => "prof"]);
        $matiere = new Matieres();
        $form = $this->createForm(MatieresType::class, $matiere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $matiere->setClasse($request->request->get("classe"));
            $matiere->setProfesseur($request->request->get("prof"));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($matiere);
            $entityManager->flush();
           
            return $this->redirectToRoute('matieres_index');
        }
        return $this->render('matieres/new.html.twig', [
            'matiere' => $matiere,
            'prof' =>  $listess,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="matieres_show", methods={"GET"})
     */
    public function show(Matieres $matiere): Response
    {
        return $this->render('matieres/show.html.twig', [
            'matiere' => $matiere,
        ]);
    }
    
    
    /**
     * @Route("/{id}/edit", name="matieres_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Matieres $matiere,UserRepository $listes): Response
    {
        $listess=array();
        $listess= $listes->findBy(
            ["niveau" => "prof"]);
        $form = $this->createForm(MatieresType::class, $matiere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           
            $matiere->setClasse($request->request->get("classe"));
            $matiere->setProfesseur($request->request->get("prof"));
            $this->getDoctrine()->getManager()->persist($matiere);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('matieres_index');
        }

        return $this->render('matieres/edit.html.twig', [
            'matiere' => $matiere,
            'prof' =>  $listess,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="matieres_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Matieres $matiere): Response
    {
        if ($this->isCsrfTokenValid('delete'.$matiere->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($matiere);
            $entityManager->flush();
        }

        return $this->redirectToRoute('matieres_index');
    }
}
