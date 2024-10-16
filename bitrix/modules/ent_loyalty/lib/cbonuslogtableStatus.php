<?php

namespace Enterego\Osh\Loyalty;

/**
 * �������������� ������� ��� ���������� � ����������� ��� ��������� �������.
 *
 * @package Enterego\Osh\Loyalty
 */
class CBonusLogTableStatus
{
    /**
     * ������ ����������� ����������.
     * ������������ �� ������� 1� (���� �������� �� 1C)
     */
    const FINAL = 0;

    /**
     * ���������� ��������.
     * - ����� ��� ������� ��������, �� ��� ������� (�� ������� 1� ��� �����)
     */
    const CANCEL = 1;

    /**
     * ������ ����������������� ���������� ������� �� �����.
     * ��������� ��������� ���������� � ������� 1C.
     */
    const ADD = 10;

    /**
     * ������ ����������������� �������� ������� �� �����.
     * ��������� ��������� ���������� � ������� 1C �, ��������,
     * ��������� �������������.
     */
    const SPEND_NEW = 20;

    /**
     * ������ ����������������� �������� ������� �� �����.
     * ��������� � ������ ������� ������ �� 1C ����� ������������� ��� ������
     * �� ���� ���������� �� ������� 1�.
     */
    const SPEND_PENDING = 21;
}