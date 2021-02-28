<?php

namespace App\Controller;

use App\Entity\ShopCart;
use App\Entity\ShopItems;
use App\Entity\ShopOrder;
use App\Form\OrderFormType;
use App\Repository\ShopCartRepository;
use App\Repository\ShopItemsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    private SessionInterface $session;

    /**
     * IndexController constructor.
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
        $this->session->start();
    }


    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        return $this->render(
            'index/index.html.twig',
            [
                'title' => 'Главная страница',
            ]
        );
    }

    /**
     * @Route("/shop/list", name="shopList")
     * @param ShopItemsRepository $itemsRepository
     * @return Response
     */
    public function shopList(ShopItemsRepository $itemsRepository): Response
    {
        $items = $itemsRepository->findAll();

        return $this->render(
            'index/shopList.html.twig',
            [
                'title' => 'SHOP LIST',
                'items' => $items,
            ]
        );
    }

    /**
     * @Route("/shop/cart/add/{id<\d+>}", name="shopCartAdd")
     *
     * @param ShopItems $shopItems
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function shopCartAdd(ShopItems $shopItems, EntityManagerInterface $em): Response
    {
        $sessionId = $this->session->getId();

        $shopCart = (new ShopCart())
            ->setShopItem($shopItems)
            ->setCount(1)
            ->setSessionId($sessionId);

        $em->persist($shopCart);
        $em->flush();

        return $this->redirectToRoute('shopItem', ['id' => $shopItems->getId()]);
    }

    /**
     * @Route("/shop/item/{id<\d+>}", name="shopItem")
     *
     * @param ShopItems $shopItems
     * @return Response
     */
    public function shopItem(ShopItems $shopItems): Response
    {
        return $this->render(
            'index/shopItem.html.twig',
            [
                'title' => $shopItems->getTitle(),
                'description' => $shopItems->getDescription(),
                'price' => $shopItems->getPrice(),
                'id' => $shopItems->getId(),
            ]
        );
    }

    /**
     * @Route("/shop/cart", name="shopCart")
     *
     * @param ShopCartRepository $cartRepository
     * @return Response
     */
    public function shopCart(ShopCartRepository $cartRepository): Response
    {
        $session = $this->session->getId();
        $items = $cartRepository->findBy(['sessionId' => $session]);

        return $this->render(
            'index/shopCart.html.twig',
            [
                'title' => 'Корзина',
                'items' => $items,
            ]
        );
    }

    /**
     * @Route("/shop/order", name="shopOrder")
     *
     * @param Request $request
     * @return Response
     */
    public function shopOrder(Request $request, EntityManagerInterface $em): Response
    {

        // just setup a fresh $task object (remove the example data)
        $shopOrder = new ShopOrder();

        $form = $this->createForm(OrderFormType::class, $shopOrder);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $shopOrder = $form->getData();

            if ($shopOrder instanceof ShopOrder) {
                $sessionId = $this->session->getId();
                $shopOrder->setStatus(ShopOrder::STATUS_NEW_ORDER);
                $shopOrder->setSessionId($sessionId);
                $em->persist($shopOrder);
                $em->flush();
                //session_regenerate_id
                $this->session->migrate();
            }

            return $this->redirectToRoute('index');
        }


        return $this->render(
            'index/shopOrder.html.twig',
            [
                'title' => 'Оформление заказа',
                'form' => $form->createView(),
            ]
        );
    }
}
