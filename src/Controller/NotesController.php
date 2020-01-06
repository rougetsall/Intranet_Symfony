<?php

namespace App\Controller;

use App\Entity\Notes;
use App\Form\Notes1Type;
use App\Entity\User;
use App\Repository\NotesRepository;
use App\Repository\MatieresRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/notes")
 */
class NotesController extends AbstractController
{ 
    /**
     * @Route("/", name="notes_index",  methods={"GET","POST"})
     */
    public function index(NotesRepository $notesRepository,UserRepository $listes,MatieresRepository $matieres): Response
    { 
        $em = $this->get('doctrine')->getManager()->createQueryBuilder();
        $listess=array();
        $profilis= $em->select('p.classe,p.matiere')
              ->from('App\Entity\Matieres', 'p')
              ->andWhere('p.professeur = :val')
              ->setParameter('val', $this->getUser()->getNom())
              ->getQuery()
              ->getResult();
        
       for ($i=0; $i < count($profilis) ; $i++) { 
            $emp = $this->get('doctrine')->getManager()->createQueryBuilder();
            $lis=$emp->select('u.nom')
                ->from('App\Entity\User', 'u')
                ->andWhere('u.niveau = :val')
                ->setParameter('val',$profilis[$i]["classe"])
                ->getQuery()
                ->getResult();
        
            if (count($lis)!=0) {
                for ($j=0; $j < count($lis) ; $j++) { 
                    if (!in_array($lis[$j],$listess)) {
                        array_push($listess,$lis[$j]);
                    }
                    
                } 
            }
        }
       
        $list=array();
        for ($i=0; $i < count($listess) ; $i++) {
            for ($j=0; $j < count($profilis) ; $j++) { 
                $lis= $notesRepository->findBy(
                    ["matiere"=>$profilis[$j]["matiere"],
                    "nom_etudiant" =>  $listess[$i]["nom"]]);
                if (count($lis)!=0) {
                    
                    $list=array_merge($list,$lis);
                }
                
            }
        } 
        if(in_array('ROLE_ADMIN', $this->getUser()->getRoles()))
        {
        return $this->render('notes/index.html.twig', [
            'notes' => $notesRepository->findAll(),
        ]);
        }elseif(in_array('ROLE_USER_PROF', $this->getUser()->getRoles())){
            
            return $this->render('notes/index.html.twig', [
                'notes' => $list
            ]);
        }
        else{
            $notes=array();
            $em = $this->get('doctrine')->getManager()->createQueryBuilder();
            $notes= $em->select('n.note')
              ->from('App\Entity\Notes', 'n')
              ->andWhere('n.nom_etudiant = :val')
              ->setParameter('val', $this->getUser()->getNom())
              ->getQuery()
              ->getResult();
              $notee=0;
            for ($i=0; $i <count($notes) ; $i++) { 
                $notee+=$notes[$i]["note"];
            }
        
            $moyen=round($notee / count($notes), 2, PHP_ROUND_HALF_UP );
            return $this->render('notes/index.html.twig', [
                'notes' => $notesRepository->findBy(
                ["nom_etudiant"=>$this->getUser()->getNom()]),
                "moyen"=>$moyen
            ]);
        }
    }

    /**
     * @Route("/new", name="notes_new", methods={"GET","POST"})
     */
    public function new(Request $request,UserRepository $listes,MatieresRepository $matieres): Response
    {    
        $note = new Notes();
        $form = $this->createForm(Notes1Type::class, $note);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $request = Request::createFromGlobals();
            $request = new Request(
                $_GET,
                $_POST
            );
            $note->setNomEtudiant( $request->request->get("nom_etudiant"));
            $note->setMatiere( $request->request->get("matieres"));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($note);
            $entityManager->flush();

            return $this->redirectToRoute('notes_index');
        }

        $em = $this->get('doctrine')->getManager()->createQueryBuilder();;
        $listess=array();
        
        $profilis= $em->select('p.classe')
              ->from('App\Entity\Matieres', 'p')
              ->andWhere('p.professeur = :val')
              ->setParameter('val', $this->getUser()->getNom())
              ->getQuery()
              ->getResult();
      
              for ($i=0; $i < count($profilis) ; $i++) { 
                $lis= $listes->findBy(
                        ["niveau" => $profilis[$i]["classe"]]);
                    if (count($lis)!=0) {
                        for ($j=0; $j < count($lis) ; $j++) { 
                            if (!in_array($lis[$j],$listess)) {
                                array_push($listess,$lis[$j]);
                            }
                            
                        }
                    }
                
               }
        
       if(in_array('ROLE_USER_PROF', $this->getUser()->getRoles()))
        {
            return $this->render('notes/new.html.twig', [
                'matieres' => $matieres->findBy(
                ["professeur"=>$this->getUser()->getNom()]),
                'note' => $note,
                'liste'=>$listess,
                'form' => $form->createView(),
            ]);
        }else{
            $em = $this->get('doctrine')->getManager()->createQueryBuilder();
            $listess=array();
            $profilis= $em->select('p.classe')
                ->from('App\Entity\Matieres', 'p')
                ->getQuery()
                ->getResult();
        
            for ($i=0; $i < count($profilis) ; $i++) { 
                $lis= $listes->findBy(
                        ["niveau" => $profilis[$i]["classe"]]);
                    if (count($lis)!=0) {
                        for ($j=0; $j < count($lis) ; $j++) { 
                            if (!in_array($lis[$j],$listess)) {
                                array_push($listess,$lis[$j]);
                            }
                            
                        }
                    }
                
            }
            return $this->render('notes/new.html.twig', [
                'note' => $note,
                'matieres' => $matieres->findAll(),
                'liste'=>$listess,
                'form' => $form->createView(),
            ]);
        }
        
    }

    /**
     * @Route("/{id}", name="notes_show", methods={"GET"})
     */
    public function show(Notes $note): Response
    {
        return $this->render('notes/show.html.twig', [
            'note' => $note,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="notes_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Notes $note,MatieresRepository $matieres,UserRepository $listes): Response
    {
        $form = $this->createForm(Notes1Type::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $request = Request::createFromGlobals();
            $request = new Request(
                $_GET,
                $_POST
            );
            $note->setNomEtudiant( $request->request->get("nom_etudiant"));
            $note->setMatiere( $request->request->get("matieres"));
            $this->getDoctrine()->getManager()->persist($note);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('notes_index');
        }

        $em = $this->get('doctrine')->getManager()->createQueryBuilder();;
            $listess=array();
            $profilis= $em->select('p.classe')
                ->from('App\Entity\Matieres', 'p')
                ->getQuery()
                ->getResult();
        
            for ($i=0; $i < count($profilis) ; $i++) { 
                $lis= $listes->findBy(
                        ["niveau" => $profilis[$i]["classe"]]);
                    if (count($lis)!=0) {
                        for ($j=0; $j < count($lis) ; $j++) { 
                            if (!in_array($lis[$j],$listess)) {
                                array_push($listess,$lis[$j]);
                            }
                            
                        }
                    }
                
            }
        return $this->render('notes/edit.html.twig', [
            'note' => $note,
            'matieres' => $matieres->findAll(),
            'liste'=>$listess,
            'form' => $form->createView(),
        ]);
    }
    
    /**
     * @Route("/{id}", name="notes_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Notes $note): Response
    {
        if ($this->isCsrfTokenValid('delete'.$note->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($note);
            $entityManager->flush();
        }

        return $this->redirectToRoute('notes_index');
    }
}
