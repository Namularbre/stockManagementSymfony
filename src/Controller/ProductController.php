<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Form\UpdateProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/products')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'app_products', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();

        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/product/{id}', name: 'app_product', methods: ['GET'])]
    public function getProduct(ProductRepository $productRepository, int $id): Response
    {
        $product = $productRepository->findOneBy(['id' => $id]);

        if (isset($product)) {
            return $this->render('product/product.html.twig', [
                'product' => $product,
            ]);
        }

        throw $this->createNotFoundException('Product not found');
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function newProduct(EntityManagerInterface $entityManager, Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->persist($product);
                $entityManager->flush();

                $this->addFlash('success', 'Product added successfully');
            } else {
                $error = $form->getErrors(true)[0]->getMessage();
                $this->addFlash('danger', $error);
            }
            return $this->redirectToRoute('app_product_new');
        }

        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'app_product_delete', methods: ['DELETE'])]
    public function deleteProduct(EntityManagerInterface $entityManager, int $id): Response
    {
        $product = $entityManager->getRepository(Product::class)->findOneBy(['id' => $id]);

        if (isset($product)) {
            $entityManager->remove($product);
            $entityManager->flush();

            return new Response('Product removed successfully', Response::HTTP_OK);
        }

        throw $this->createNotFoundException('Product not found.');
    }

    #[Route('/update/{id}', name: 'app_product_update', methods: ['GET', 'PUT'])]
    public function updateProduct(EntityManagerInterface $entityManager, int $id, Request $request): Response
    {
        $product = $entityManager->getRepository(Product::class)->findOneBy(['id' => $id]);

        if (!isset($product)) {
            throw $this->createNotFoundException('Product not found');
        }

        $form = $this->createForm(ProductType::class, $product, [
            'method' => Request::METHOD_PUT
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->flush();

                $this->addFlash('success', 'Product updated');
            } else {
                $error = $form->getErrors(true)[0]->getMessage();
                $this->addFlash('danger', $error);
            }
            return $this->redirectToRoute('app_product_update', [
                'id' => $product->getId()
            ]);
        }

        return $this->render('product/update.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }
}
