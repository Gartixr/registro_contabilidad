<?php
// src/Controller/UserController.php
namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use \Datetime;

# Controlador principal del Usuario
class UserController extends AbstractController
{
    # Método principal donde cargamos el formulario de la creacion del usuario y la tabla de los usuarios
    public function mainUser(Request $request, EntityManagerInterface $entityManager): Response
    {

        $tableInfo = ['Nombre', 'Correo electrónico', 'Teléfono de contacto', 'Proveedor', 'Activo', 'Aciones'];
        $user = new User();

        $form = self::generateForm($user, false);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $timestamp = time(); 
            $dateTime = new DateTime(date('Y-m-d H:i:s', $timestamp));
            $user->setCreateDate($dateTime);
            $user->setEditDate($dateTime);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirect($request->getUri());

        }

        $users = $entityManager->getRepository(User::class)->findAll();

        if (!$user) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        return $this->render('mainUsers.html.twig', [
            'table_info' => $tableInfo,
            'user_data' => $users,
            'form' => $form->createView(),
        ]);
    }


    # Método para visualizar un usuario por ID
    public function viewUser(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $userData = $entityManager->getRepository(User::class)->findOneBy(['id' => $id]);
        return $this->render('viewUser.html.twig', [
            'user_data' => $userData
        ]);
    }

    # Método para editar un usario
    public function editUser(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $userData = $entityManager->getRepository(User::class)->findOneBy(['id' => $id]);
        $form = self::generateForm($userData, true);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            
            $timestamp = time(); 
            $dateTime = new DateTime(date('Y-m-d H:i:s', $timestamp));

            $user->setEditDate($dateTime);

            $entityManager->flush();

            return $this->redirect("/");

        }

        return $this->render('editUser.html.twig', ['form' => $form->createView()]);
    }

    # Método para borrar un usuario
    public function deleteUser(int $id, EntityManagerInterface $entityManager): Response {
        $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $id]);
        
        try {
            $entityManager->remove($user);
            $entityManager->flush();
        } catch (\Throwable $th) {
            //throw $th;
        }

        return $this->forward('App\Controller\UserController::mainUser');
    }
    
    # Método que utilizamos para generar un formulario
    private function generateForm($user, bool $edit)
    {

        # Pequeño controlador para cambiar el texto del botón según necesidad
        $btnText = "Crear";
        if ($edit) {
            $btnText = "Editar";
        }

        return $this->createFormBuilder($user)
        ->add('name', TextType::class, array('label' => 'Nombre'))
        ->add('email', EmailType::class, array('label' => 'Correo'))
        ->add('tlf', TextType::class, array('label' => 'Teléfono'))
        ->add('proveer', ChoiceType::class, [
            'choices' => [
                'Hotel' => 'Hotel',
                'Pista' => 'Pista',
                'Complemento' => 'Complemento'
            ],'label' => 'Proveedor'
        ])
        ->add('active', ChoiceType::class, [
            'choices' => [
                'Si' => true,
                'No' => false
            ], 'label' => 'Activo'
        ])
        ->add('save', SubmitType::class, ['label' => $btnText])
        ->getForm();
    }
}