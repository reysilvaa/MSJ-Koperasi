# Activity Diagram - Member Fee Management (Kelola Iuran Anggota)

```mermaid
flowchart TD
    %% Initial Node
    Start([●]) --> SelectFeeType
    
    %% Member Lane
    subgraph MemberLane [" ANGGOTA "]
        SelectFeeType[Select Fee<br/>Type]
        SelectPaymentMethod[Select Payment<br/>Method]
        PayInitialFee[Pay Initial Fee<br/>Rp 50.000]
        PayMonthlyFee[Pay Monthly Fee<br/>Rp 25.000]
        ConfirmPayment[Confirm<br/>Payment]
        UploadPaymentProof[Upload Payment<br/>Proof]
        ViewFeeStatus[View Fee<br/>Status]
        PaymentCompleted[Payment<br/>Completed]
    end
    
    %% System Lane
    subgraph SystemLane [" FEE SYSTEM "]
        LoadFeeOptions[Load Fee<br/>Options]
        ProcessFeePayment[Process Fee<br/>Payment]
        ValidatePayment[Validate<br/>Payment]
        GenerateReceipt[Generate<br/>Receipt]
        UpdateFeeStatus[Update Fee<br/>Status]
        ScheduleReminders[Schedule Fee<br/>Reminders]
        SendFeeReminders[Send Dashboard<br/>Reminders]
        NotifyPaymentComplete[Notify Payment<br/>Complete]
        GenerateMonthlyStatement[Generate Monthly<br/>Statement]
    end
    
    %% Database Lane
    subgraph DatabaseLane [" DATABASE "]
        QueryMemberFeeStatus[Query Member<br/>Fee Status]
        CreateFeeRecord[Create Fee<br/>Record]
        UpdatePaymentStatus[Update Payment<br/>Status]
        LogFeeTransaction[Log Fee<br/>Transaction]
        UpdateMemberBalance[Update Member<br/>Balance]
        CreateJournalEntry[Create Journal<br/>Entry]
    end
    
    %% Admin Lane
    subgraph AdminLane [" ADMIN "]
        MonitorFeePayments[Monitor Fee<br/>Payments]
        UpdateManualPayment[Update Manual<br/>Payment]
        GenerateFeeReport[Generate Fee<br/>Report]
        SendFeeReminder[Send Fee<br/>Reminder]
        ValidateProofPayment[Validate Proof<br/>Payment]
    end
    
    %% Banking Lane
    subgraph BankingLane [" BANKING SYSTEM "]
        ProcessBankTransfer[Process Bank<br/>Transfer]
        ValidateTransferAmount[Validate Transfer<br/>Amount]
        ConfirmTransferReceived[Confirm Transfer<br/>Received]
    end
    
    %% Flow connections
    Start --> SelectFeeType
    SelectFeeType --> LoadFeeOptions
    LoadFeeOptions --> QueryMemberFeeStatus
    QueryMemberFeeStatus --> FeeTypeDecision{Fee Type?}
    
    FeeTypeDecision -->|Initial Fee| PayInitialFee
    FeeTypeDecision -->|Monthly Fee| PayMonthlyFee
    
    PayInitialFee --> SelectPaymentMethod
    PayMonthlyFee --> SelectPaymentMethod
    
    SelectPaymentMethod --> PaymentMethodDecision{Payment Method?}
    PaymentMethodDecision -->|Bank Transfer| ProcessBankTransfer
    PaymentMethodDecision -->|Cash| ConfirmPayment
    PaymentMethodDecision -->|Salary Deduction| ProcessFeePayment
    
    ProcessBankTransfer --> ValidateTransferAmount
    ValidateTransferAmount --> ConfirmTransferReceived
    ConfirmTransferReceived --> UploadPaymentProof
    
    UploadPaymentProof --> ValidateProofPayment
    ValidateProofPayment --> ValidatePayment
    
    ConfirmPayment --> ValidatePayment
    ProcessFeePayment --> ValidatePayment
    
    ValidatePayment --> CreateFeeRecord
    CreateFeeRecord --> UpdatePaymentStatus
    UpdatePaymentStatus --> LogFeeTransaction
    LogFeeTransaction --> UpdateMemberBalance
    UpdateMemberBalance --> CreateJournalEntry
    
    CreateJournalEntry --> GenerateReceipt
    GenerateReceipt --> UpdateFeeStatus
    UpdateFeeStatus --> NotifyPaymentComplete
    NotifyPaymentComplete --> PaymentCompleted
    PaymentCompleted --> End([●])
    
    %% Admin monitoring flow
    MonitorFeePayments --> GenerateFeeReport
    GenerateFeeReport --> ScheduleReminders
    ScheduleReminders --> SendFeeReminders
    SendFeeReminders --> SendFeeReminder
    SendFeeReminder --> GenerateMonthlyStatement
    GenerateMonthlyStatement --> EndAdmin([●])
    
    %% Styling for swimlanes
    classDef memberStyle fill:#e1f5fe,stroke:#01579b,stroke-width:2px
    classDef systemStyle fill:#f3e5f5,stroke:#4a148c,stroke-width:2px  
    classDef databaseStyle fill:#e8f5e8,stroke:#1b5e20,stroke-width:2px
    classDef adminStyle fill:#e3f2fd,stroke:#0d47a1,stroke-width:2px
    classDef bankingStyle fill:#fff3e0,stroke:#e65100,stroke-width:2px
    
    class SelectFeeType,SelectPaymentMethod,PayInitialFee,PayMonthlyFee,ConfirmPayment,UploadPaymentProof,ViewFeeStatus,PaymentCompleted memberStyle
    class LoadFeeOptions,ProcessFeePayment,ValidatePayment,GenerateReceipt,UpdateFeeStatus,ScheduleReminders,SendFeeReminders,NotifyPaymentComplete,GenerateMonthlyStatement systemStyle
    class QueryMemberFeeStatus,CreateFeeRecord,UpdatePaymentStatus,LogFeeTransaction,UpdateMemberBalance,CreateJournalEntry databaseStyle
    class MonitorFeePayments,UpdateManualPayment,GenerateFeeReport,SendFeeReminder,ValidateProofPayment adminStyle
    class ProcessBankTransfer,ValidateTransferAmount,ConfirmTransferReceived bankingStyle
```

## Penjelasan Member Fee Management

Diagram ini menunjukkan pengelolaan iuran anggota dengan berbagai metode pembayaran:

### 👥 ANGGOTA (Member Lane)
- Pilih jenis iuran (awal/bulanan)
- Pilih metode pembayaran
- Upload bukti pembayaran jika transfer
- View status pembayaran iuran

### 🤖 FEE SYSTEM (System Lane)
- Load opsi pembayaran iuran
- Process berbagai metode pembayaran
- Generate receipt otomatis
- Schedule reminder H-3 jatuh tempo
- Dashboard notifications

### 🗄️ DATABASE (Database Lane)
- Track status iuran per anggota
- Create fee records
- Update payment status
- Maintain complete audit trail
- Journal entry untuk accounting

### 👤 ADMIN (Admin Lane)
- Monitor pembayaran iuran semua anggota
- Validate proof of payment
- Update manual payment jika perlu
- Generate laporan iuran
- Send reminder ke anggota

### 🏦 BANKING SYSTEM (Banking Lane)
- Process transfer bank
- Validate transfer amount
- Confirm transfer received

### Fitur Utama
- **Multiple Payment Methods**: Transfer, cash, salary deduction
- **Automated Reminders**: Dashboard alerts H-3 jatuh tempo
- **Receipt Generation**: Auto-generate receipt setelah payment
- **Admin Monitoring**: Dashboard untuk monitor semua iuran
- **Audit Trail**: Complete logging semua transaksi iuran
- **Integration**: Terintegrasi dengan payroll untuk potong gaji
