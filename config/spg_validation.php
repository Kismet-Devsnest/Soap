<?php

return array (
  'operations' => 
  array (
    'GetAccountVerification' => 
    array (
      'strAccountNo' => 'nullable|string',
      'strMobileNo' => 'nullable|string',
    ),
    'GetUpdateAfterCashReceived' => 
    array (
      'OwnerCode' => 'nullable|string',
      'TransactionNo' => 'nullable|string',
      'TransactionDate' => 'nullable|string',
      'isEncPwd' => 'required|boolean',
    ),
    'DailyStTransaction' => 
    array (
      'OwnerCode' => 'nullable|string',
      'ReferenceDate' => 'nullable|string',
      'isEncPwd' => 'required|boolean',
    ),
    'TransactionDetails' => 
    array (
      'OwnerCode' => 'nullable|string',
      'ReferenceDate' => 'nullable|string',
      'RequiestNo' => 'nullable|string',
      'isEncPwd' => 'required|boolean',
    ),
    'TransactionVerification' => 
    array (
      'OwnerCode' => 'nullable|string',
      'ReferenceDate' => 'nullable|string',
      'RequiestNo' => 'nullable|string',
      'isEncPwd' => 'required|boolean',
    ),
    'TransactionVerificationWithRefNo' => 
    array (
      'OwnerCode' => 'nullable|string',
      'RefNo' => 'nullable|string',
      'isEncPwd' => 'required|boolean',
    ),
    'DailySpTransaction' => 
    array (
      'OwnerCode' => 'nullable|string',
      'ReferenceDate' => 'nullable|string',
      'isEncPwd' => 'required|boolean',
    ),
    'RequestInfo' => 
    array (
      'Msisdn' => 'nullable|string',
    ),
    'GetSessionKey' => 
    array (
      'strUserId' => 'nullable|string',
      'strPassKey' => 'nullable|string',
      'strRequestId' => 'nullable|string',
      'strAmount' => 'nullable|string',
      'strTranDate' => 'nullable|string',
      'strAccounts' => 'nullable|string',
    ),
  ),
);
