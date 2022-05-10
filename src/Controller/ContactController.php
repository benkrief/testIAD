<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\ContactRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/contact")
 */
class ContactController extends AbstractController
{
    //Permet de visualiser la liste des contacts
    /**
     * @Route("/", name="app_contact_index", methods={"GET"})
     */
    public function index(ContactRepository $contactRepository): Response
    {
        return $this->json($contactRepository->findAll());
    }

    //Permet de visualiser uniquement la liste des contacts avec un filtre (actif ou inactif)
    /**
     * @Route("/active/{bool}", name="app_contact_active", methods={"GET"})
     */
    public function active(ContactRepository $contactRepository, bool $bool): Response
    {
        return $this->json($contactRepository->findByActive($bool));
    }

    //Permet de créer un contact avec du JSON
    /**
     * @Route("/new", name="app_contact_new", methods={"GET", "POST"})
     */
    public function new(Request $request, ContactRepository $contactRepository): Response
    {
        $contact = $this->setJsonToEntity(new Contact(), $request);
        if($contact->getAge()>17){
            $contactRepository->add($contact, true);
            return new Response('Contact added', Response::HTTP_CREATED);
        }
        else{
            return new Response('Age not valid', Response::HTTP_FORBIDDEN);
        }
    }
    //Permet d'afficher un contact précis
    /**
     * @Route("/{id}", name="app_contact_show", methods={"GET"})
     */
    public function show(Contact $contact): Response
    {
        return $this->json($contact);
    }

    //Permet de modifier un contact précis
    /**
     * @Route("/{id}/edit", name="app_contact_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Contact $contact, ContactRepository $contactRepository): Response
    {
        $newContact = $this->setJsonToEntity($contactRepository->find($contact->getId()), $request);
        if($newContact->getAge()>17){
            $contactRepository->add($newContact, true);
            return new Response('Contact updated', Response::HTTP_CREATED);
        }
        else{
            return new Response('Age not valid', Response::HTTP_FORBIDDEN);
        }


    }

    //Permet de désactiver le compte d'un contact précis
    /**
     * @Route("/{id}/desactive", name="app_contact_desactive", methods={"GET", "POST"})
     */
    public function desactive(Contact $contact,ContactRepository $contactRepository): Response
    {
        $contactRepository->find($contact->getId())->setAccount(false);
        $contactRepository->add($contact, true);
        return new Response('Compte inactif', Response::HTTP_OK);
    }

    //Permet de supprimer le compte d'un contact précis
    /**
     * @Route("/{id}/delete", name="app_contact_delete", methods={"POST"})
     */
    public function delete(int $id, ContactRepository $contactRepository): Response
    {
        if($contactRepository->find($id)!=null){
            $contact = $contactRepository->find($id);
            $contactRepository->remove($contact, true);
            return new Response('Contact deleted', Response::HTTP_OK);
        }
        return new Response('Contact not found', Response::HTTP_NOT_FOUND);

    }

    //Permet de récupérer les information d'un contact
    public function setJsonToEntity(Contact $contact, Request $request): Contact
    {
        $data = json_decode($request->getContent(), true);
        $contact->setFirstname($data['firstname']);
        $contact->setLastname($data['lastname']);
        $contact->setEmail($data['email']);
        $contact->setPhone($data['phone']);
        $contact->setAdress($data['adress']);
        $contact->setAge($data['age']);
        $contact->setAccount($data['account']);
        return $contact;
    }
}
