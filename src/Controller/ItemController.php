<?php

namespace App\Controller;

use App\Entity\Item;
use App\Form\ItemType;
use App\Repository\ItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/')]
class ItemController extends AbstractController
{
    public const COUNT_PER_PAGE = 2;

    #[Route('/{page}/{sort}', name: 'item_index', methods: ['GET'], requirements: ['page' => '\d+'])]
    public function list(ItemRepository $itemRepository, int $page = 1, string $sort = 'name'): Response
    {
        $pagesCount = ceil($itemRepository->count([]) / static::COUNT_PER_PAGE);

        $start = ($page - 1) * static::COUNT_PER_PAGE;
        $items = $itemRepository->findItems($start, static::COUNT_PER_PAGE, $sort);

        return $this->render('item/index.html.twig', [
            'items' => $items,
            'pagesCount' => $pagesCount,
            'sortBy' => $sort,
        ]);
    }

    #[Route('/new', name: 'item_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $item = new Item();
        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($item);
            $entityManager->flush();

            return $this->redirectToRoute('item_index');
        }

        return $this->render('item/new.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/search', name: 'item_search', methods: ['GET'])]
    public function search(Request $request, ItemRepository $itemRepository): Response
    {
        $name = $request->query->get('name');

        $items = $itemRepository->findByName($name);

        return $this->render('item/search.html.twig', [
            'items' => $items,
        ]);
    }

    #[Route('/{id}', name: 'item_show', methods: ['GET'])]
    public function show(Item $item): Response
    {
        return $this->render('item/show.html.twig', [
            'item' => $item,
        ]);
    }

    #[Route('/{id}/edit', name: 'item_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Item $item): Response
    {
        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('item_index');
        }

        return $this->render('item/edit.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'item_delete', methods: ['POST'])]
    public function delete(Request $request, Item $item): Response
    {
        if ($this->isCsrfTokenValid('delete'.$item->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($item);
            $entityManager->flush();
        }

        return $this->redirectToRoute('item_index');
    }
}
