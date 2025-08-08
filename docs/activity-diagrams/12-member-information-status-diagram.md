# Activity Diagram - Member Information & Status

```mermaid
flowchart TD
    %% Initial Node
    Start([●]) --> AccessMemberPortal
    
    %% Member Lane
    subgraph MemberLane [" ANGGOTA "]
        AccessMemberPortal[Access Member<br/>Portal]
        SelectInformationMenu[Select Information<br/>Menu]
        ViewLoanStatus[View Loan<br/>Status]
        ViewPaymentHistory[View Payment<br/>History]
        ViewFeeStatus[View Fee<br/>Status]
        DownloadStatements[Download<br/>Statements]
    end
    
    %% System Lane
    subgraph SystemLane [" INFORMATION SYSTEM "]
        LoadMemberPortal[Load Member<br/>Portal]
        AuthenticateMember[Authenticate<br/>Member]
        LoadMemberDashboard[Load Member<br/>Dashboard]
        ProcessInformationRequest[Process Information<br/>Request]
        CalculateCurrentBalances[Calculate Current<br/>Balances]
        GeneratePaymentSchedule[Generate Payment<br/>Schedule]
        GenerateStatements[Generate<br/>Statements]
        FormatDisplayData[Format Display<br/>Data]
    end
    
    %% Database Lane
    subgraph DatabaseLane [" DATABASE "]
        QueryMemberProfile[Query Member<br/>Profile]
        QueryActiveLoan[Query Active<br/>Loan]
        QueryPaymentHistory[Query Payment<br/>History]
        QueryFeeStatus[Query Fee<br/>Status]
        LogInformationAccess[Log Information<br/>Access]
    end
    
    %% Reporting Lane
    subgraph ReportingLane [" REPORTING "]
        GenerateLoanStatement[Generate Loan<br/>Statement]
        GeneratePaymentReport[Generate Payment<br/>Report]
        GenerateFeeStatement[Generate Fee<br/>Statement]
        CompileMemberReport[Compile Member<br/>Report]
        FormatPDFOutput[Format PDF<br/>Output]
    end
    
    %% Flow connections - Portal Access
    Start --> AccessMemberPortal
    AccessMemberPortal --> LoadMemberPortal
    LoadMemberPortal --> AuthenticateMember
    AuthenticateMember --> QueryMemberProfile
    QueryMemberProfile --> LoadMemberDashboard
    LoadMemberDashboard --> SelectInformationMenu
    
    SelectInformationMenu --> MenuDecision{Information Menu?}
    
    %% Flow connections - Loan Status
    MenuDecision -->|Loan Status| ViewLoanStatus
    ViewLoanStatus --> ProcessInformationRequest
    ProcessInformationRequest --> QueryActiveLoan
    QueryActiveLoan --> CalculateCurrentBalances
    CalculateCurrentBalances --> FormatDisplayData
    FormatDisplayData --> EndLoanStatus([●])
    
    %% Flow connections - Payment History
    MenuDecision -->|Payment History| ViewPaymentHistory
    ViewPaymentHistory --> QueryPaymentHistory
    QueryPaymentHistory --> GeneratePaymentSchedule
    GeneratePaymentSchedule --> GeneratePaymentReport
    GeneratePaymentReport --> DownloadStatements
    DownloadStatements --> FormatPDFOutput
    FormatPDFOutput --> EndPaymentHistory([●])
    
    %% Flow connections - Fee Status
    MenuDecision -->|Fee Status| ViewFeeStatus
    ViewFeeStatus --> QueryFeeStatus
    QueryFeeStatus --> GenerateFeeStatement
    GenerateFeeStatement --> EndFeeStatus([●])
    
    %% Flow connections - Comprehensive Report
    EndLoanStatus --> CompileMemberReport
    EndPaymentHistory --> CompileMemberReport
    EndFeeStatus --> CompileMemberReport
    CompileMemberReport --> GenerateLoanStatement
    GenerateLoanStatement --> LogInformationAccess
    LogInformationAccess --> End([●])
    
    %% Styling for swimlanes
    classDef memberStyle fill:#e1f5fe,stroke:#01579b,stroke-width:2px
    classDef systemStyle fill:#f3e5f5,stroke:#4a148c,stroke-width:2px  
    classDef databaseStyle fill:#e8f5e8,stroke:#1b5e20,stroke-width:2px
    classDef reportingStyle fill:#f1f8e9,stroke:#33691e,stroke-width:2px
    
    class AccessMemberPortal,SelectInformationMenu,ViewLoanStatus,ViewPaymentHistory,ViewFeeStatus,DownloadStatements memberStyle
    class LoadMemberPortal,AuthenticateMember,LoadMemberDashboard,ProcessInformationRequest,CalculateCurrentBalances,GeneratePaymentSchedule,GenerateStatements,FormatDisplayData systemStyle
    class QueryMemberProfile,QueryActiveLoan,QueryPaymentHistory,QueryFeeStatus,LogInformationAccess databaseStyle
    class GenerateLoanStatement,GeneratePaymentReport,GenerateFeeStatement,CompileMemberReport,FormatPDFOutput reportingStyle
```

## Penjelasan Member Information & Status

Diagram ini menunjukkan portal informasi lengkap untuk anggota melihat status dan history mereka:

### 👥 ANGGOTA (Member Lane)
- Access member portal dengan authentication
- View basic loan status (outstanding balance, monthly payment)
- Check payment history lengkap
- Monitor fee status (iuran)
- Download statements dalam format PDF

### 🤖 INFORMATION SYSTEM (System Lane)
- Authenticate member access
- Load personalized dashboard
- Process basic information requests
- Calculate current balances dan outstanding
- Generate basic payment schedules
- Format display data untuk user-friendly view

### 🗄️ DATABASE (Database Lane)
- Query basic member profile
- Access active loan information (limited)
- Retrieve payment history
- Check fee payment status
- Log information access untuk audit

### 📊 REPORTING (Reporting Lane)
- Generate basic loan statements
- Create payment reports
- Compile fee statements
- Compile basic member reports
- Format PDF outputs untuk download

### Fitur Utama
- **Basic Information**: Current loan status, balances, payments (limited)
- **Payment History**: Riwayat pembayaran member
- **Fee Monitoring**: Status iuran dan tunggakan
- **Download Capability**: PDF export untuk basic statements
- **Audit Trail**: Log access informasi
- **Mobile-Friendly**: Responsive design untuk mobile access
- **Privacy Protection**: Sensitive data (SHU, eligibility) hanya untuk admin

### Information Categories
- **Loan Status**: Basic outstanding balance, monthly payment (no eligibility info)
- **Payment History**: Payment records dengan basic breakdown
- **Fee Status**: Status iuran awal, bulanan, tunggakan
- **Restricted Information**: SHU, eligibility, detailed projections (admin only)
