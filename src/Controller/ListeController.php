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


class ListeController extends AbstractController
{
    /**
     * @Route("/liste", name="liste")
     */
    public function index(UserRepository $listes,MatieresRepository $matieres)
    {
        if(in_array('ROLE_USER_PROF', $this->getUser()->getRoles()))
        {
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
       
            return $this->render('liste/index.html.twig', [
                'listess' => $listess,
                
            ]);
        }else{
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
       
            return $this->render('liste/index.html.twig', [
                'listess' => $listess,
                
            ]);

        }
    }


    /**
     * @Route("/listeprof", name="liste_prof")
     */
    public function listeprof(UserRepository $listes)
    {
        if(in_array('ROLE_ADMIN', $this->getUser()->getRoles()))
        {
            return $this->render('liste/index.html.twig', [
                'listess' => $listes->findBy(
                    ["niveau" => "prof"]),
                
            ]);
        }
    }
}
